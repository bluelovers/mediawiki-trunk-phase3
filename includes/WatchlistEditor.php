<?php

/**
 * Provides the UI through which users can perform editing
 * operations on their watchlist
 *
 * @addtogroup Watchlist
 * @author Rob Church <robchur@gmail.com>
 */
class WatchlistEditor {

	/**
	 * Editing modes
	 */
	const EDIT_CLEAR = 1;
	const EDIT_RAW = 2;
	const EDIT_NORMAL = 3;

	/**
	 * Main execution point
	 *
	 * @param User $user
	 * @param OutputPage $output
	 * @param WebRequest $request
	 * @param int $mode
	 */
	public function execute( $user, $output, $request, $mode ) {
		if( wfReadOnly() ) {
			$output->readOnlyPage();
			return;
		}
		switch( $mode ) {
			case self::EDIT_CLEAR:
				$output->setPageTitle( wfMsg( 'watchlistedit-clear-title' ) );
				if( $request->wasPosted() && $this->checkToken( $request, $user ) ) {
					$this->clearWatchlist( $user );
					$user->invalidateCache();
					$output->addHtml( wfMsgExt( 'watchlistedit-clear-done', 'parse' ) );
				} else {
					$this->showClearForm( $output, $user );
				}
				break;
			case self::EDIT_RAW:
				$output->setPageTitle( wfMsg( 'watchlistedit-raw-title' ) );
				if( $request->wasPosted() && $this->checkToken( $request, $user ) ) {
					$titles = $this->extractTitles( $request->getText( 'titles' ) );
					$this->clearWatchlist( $user );
					$this->watchTitles( $titles, $user );
					$user->invalidateCache();
					$output->addHtml( wfMsgExt( 'watchlistedit-raw-done', 'parse' ) );
				}
				$this->showRawForm( $output, $user );
				break;
			case self::EDIT_NORMAL:
				$output->setPageTitle( wfMsg( 'watchlistedit-normal-title' ) );
				if( $request->wasPosted() && $this->checkToken( $request, $user ) ) {
					$titles = $this->extractTitles( $request->getArray( 'titles' ) );
					$this->unwatchTitles( $titles, $user );
					$user->invalidateCache();
					$output->addHtml( wfMsgExt( 'watchlistedit-normal-done', 'parse',
						$GLOBALS['wgLang']->formatNum( count( $titles ) ) ) );
					$this->showTitles( $titles, $output, $user->getSkin() );
				}
				$this->showNormalForm( $output, $user );
		}
	}
	
	/**
	 * Check the edit token from a form submission
	 *
	 * @param WebRequest $request
	 * @param User $user
	 * @return bool
	 */
	private function checkToken( $request, $user ) {
		return $user->matchEditToken( $request->getVal( 'token' ), 'watchlistedit' );	
	}
	
	/**
	 * Extract a list of titles from a text list; if we're given
	 * an array, convert each item into a Title
	 *
	 * @param mixed $list
	 * @return array
	 */
	private function extractTitles( $list ) {
		if( !is_array( $list ) ) {
			$list = explode( "\n", $list );
			if( !is_array( $list ) )
				return array();
		}
		for( $i = 0; $i < count( $list ); $i++ ) {
			$list[$i] = Title::newFromText( $list[$i] );
			if( !$list[$i] instanceof Title )
				unset( $list[$i] );
		}
		return $list;
	}
	
	/**
	 * Print out a list of linked titles
	 *
	 * @param array $titles
	 * @param OutputPage $output
	 * @param Skin $skin
	 */
	private function showTitles( $titles, $output, $skin ) {
		$talk = htmlspecialchars( $GLOBALS['wgContLang']->getFormattedNsText( NS_TALK ) );
		// Do a batch existence check		
		$batch = new LinkBatch();
		foreach( $titles as $title ) {
			$batch->addObj( $title );
			$batch->addObj( $title->getTalkPage() );
		}
		$batch->execute();
		// Print out the list
		$output->addHtml( "<ul>\n" );
		foreach( $titles as $title )
			$output->addHtml( "<li>" . $skin->makeLinkObj( $title )
			. ' (' . $skin->makeLinkObj( $title->getTalkPage(), $talk ) . ")</li>\n" );
		$output->addHtml( "</ul>\n" );
	}
	
	/**
	 * Count the number of titles on a user's watchlist, excluding talk pages
	 *
	 * @param User $user
	 * @return int
	 */
	private function countWatchlist( $user ) {
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select( 'watchlist', 'COUNT(*) AS count', array( 'wl_user' => $user->getId() ), __METHOD__ );
		$row = $dbr->fetchObject( $res );
		return ceil( $row->count / 2 ); // Paranoia
	}
	
	/**
	 * Get a list of titles on a user's watchlist, excluding talk pages,
	 * and return as a two-dimensional array with namespace, title and
	 * redirect status
	 *
	 * @param User $user
	 * @return array
	 */
	private function getWatchlist( $user ) {
		$titles = array();
		$dbr = wfGetDB( DB_SLAVE );
		$uid = intval( $user->getId() );
		list( $watchlist, $page ) = $dbr->tableNamesN( 'watchlist', 'page' );
		$sql = "SELECT wl_namespace, wl_title, page_id, page_is_redirect
			FROM {$watchlist} LEFT JOIN {$page} ON ( wl_namespace = page_namespace
			AND wl_title = page_title ) WHERE wl_user = {$uid}";
		$res = $dbr->query( $sql, __METHOD__ );
		if( $res && $dbr->numRows( $res ) > 0 ) {
			$cache = LinkCache::singleton();
			while( $row = $dbr->fetchObject( $res ) ) {
				$title = Title::makeTitleSafe( $row->wl_namespace, $row->wl_title );
				if( $title instanceof Title ) {
					// Update the link cache while we're at it
					if( $row->page_id ) {
						$cache->addGoodLinkObj( $row->page_id, $title );
					} else {
						$cache->addBadLinkObj( $title );
					}
					// Ignore non-talk
					if( !$title->isTalkPage() )
						$titles[$row->wl_namespace][$row->wl_title] = $row->page_is_redirect;
				}
			}
		}
		return $titles;
	}
	
	/**
	 * Show a message indicating the number of items on the user's watchlist,
	 * and return this count for additional checking
	 *
	 * @param OutputPage $output
	 * @param User $user
	 * @return int
	 */
	private function showItemCount( $output, $user ) {
		if( ( $count = $this->countWatchlist( $user ) ) > 0 ) {
			$output->addHtml( wfMsgExt( 'watchlistedit-numitems', 'parse',
				$GLOBALS['wgLang']->formatNum( $count ) ) );
		} else {
			$output->addHtml( wfMsgExt( 'watchlistedit-noitems', 'parse' ) );
		}
		return $count;
	}
	
	/**
	 * Remove all titles from a user's watchlist
	 *
	 * @param User $user
	 */
	private function clearWatchlist( $user ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete( 'watchlist', array( 'wl_user' => $user->getId() ), __METHOD__ );
	}

	/**
	 * Add a list of titles to a user's watchlist
	 *
	 * @param array $titles
	 * @param User $user
	 */
	private function watchTitles( $titles, $user ) {
		$dbw = wfGetDB( DB_MASTER );
		$rows = array();
		foreach( $titles as $title ) {
			$rows[] = array(
				'wl_user' => $user->getId(),
				'wl_namespace' => ( $title->getNamespace() & ~1 ),
				'wl_title' => $title->getDBkey(),
				'wl_notificationtimestamp' => null,
			);
			$rows[] = array(
				'wl_user' => $user->getId(),
				'wl_namespace' => ( $title->getNamespace() | 1 ),
				'wl_title' => $title->getDBkey(),
				'wl_notificationtimestamp' => null,
			);
		}
		$dbw->insert( 'watchlist', $rows, __METHOD__, 'IGNORE' );
	}

	/**
	 * Remove a list of titles from a user's watchlist
	 *
	 * @param array $titles
	 * @param User $user
	 */
	private function unwatchTitles( $titles, $user ) {
		$dbw = wfGetDB( DB_MASTER );
		foreach( $titles as $title ) {
			$dbw->delete(
				'watchlist',
				array(
					'wl_user' => $user->getId(),
					'wl_namespace' => ( $title->getNamespace() & ~1 ),
					'wl_title' => $title->getDBkey(),
				),
				__METHOD__
			);
			$dbw->delete(
				'watchlist',
				array(
					'wl_user' => $user->getId(),
					'wl_namespace' => ( $title->getNamespace() | 1 ),
					'wl_title' => $title->getDBkey(),
				),
				__METHOD__
			);
		}
	}

	/**
	 * Show a confirmation form for users wishing to clear their watchlist
	 *
	 * @param OutputPage $output
	 * @param User $user
	 */
	private function showClearForm( $output, $user ) {
		if( ( $count = $this->showItemCount( $output, $user ) ) > 0 ) {
			$self = SpecialPage::getTitleFor( 'Watchlist' );
			$form  = Xml::openElement( 'form', array( 'method' => 'post',
				'action' => $self->getLocalUrl( 'action=clear' ) ) );
			$form .= Xml::hidden( 'token', $user->editToken( 'watchlistedit' ) );
			$form .= '<fieldset><legend>' . wfMsgHtml( 'watchlistedit-clear-legend' ) . '</legend>';
			$form .= wfMsgExt( 'watchlistedit-clear-confirm', 'parse' );
			$form .= '<p>' . Xml::submitButton( wfMsg( 'watchlistedit-clear-submit' ) ) . '</p>';
			$form .= '</fieldset></form>';
			$output->addHtml( $form );
		}
	}
	
	/**
	 * Show the standard watchlist editing form
	 *
	 * @param OutputPage $output
	 * @param User $user
	 */
	private function showNormalForm( $output, $user ) {
		if( ( $count = $this->showItemCount( $output, $user ) ) > 0 ) {
			$self = SpecialPage::getTitleFor( 'Watchlist' );
			$form  = Xml::openElement( 'form', array( 'method' => 'post',
				'action' => $self->getLocalUrl( 'action=edit' ) ) );
			$form .= Xml::hidden( 'token', $user->editToken( 'watchlistedit' ) );
			$form .= '<fieldset><legend>' . wfMsgHtml( 'watchlistedit-normal-legend' ) . '</legend>';
			$form .= wfMsgExt( 'watchlistedit-normal-explain', 'parse' );
			foreach( $this->getWatchlist( $user ) as $namespace => $pages ) {
				$form .= '<h2>' . $this->getNamespaceHeading( $namespace ) . '</h2>';
				$form .= '<ul>';
				foreach( $pages as $dbkey => $redirect ) {
					$title = Title::makeTitleSafe( $namespace, $dbkey );
					$form .= $this->buildRemoveLine( $title, $redirect, $user->getSkin() );
				}
				$form .= '</ul>';
			}
			$form .= '<p>' . Xml::submitButton( wfMsg( 'watchlistedit-normal-submit' ) ) . '</p>';
			$form .= '</fieldset></form>';
			$output->addHtml( $form );
		}
	}
	
	/**
	 * Get the correct "heading" for a namespace
	 *
	 * @param int $namespace
	 * @return string
	 */
	private function getNamespaceHeading( $namespace ) {
		return $namespace == NS_MAIN
			? wfMsgHtml( 'blanknamespace' )
			: htmlspecialchars( $GLOBALS['wgContLang']->getFormattedNsText( $namespace ) );
	}
	
	/**
	 * Build a single list item containing a check box selecting a title
	 * and a link to that title, with various additional bits
	 *
	 * @param Title $title
	 * @param bool $redirect
	 * @param Skin $skin
	 * @return string
	 */
	private function buildRemoveLine( $title, $redirect, $skin ) {
		$link = $skin->makeLinkObj( $title );
		if( $redirect )
			$link = '<span class="watchlistredir">' . $link . '</span>';
		$tools[] = $skin->makeLinkObj( $title->getTalkPage(),
			htmlspecialchars( $GLOBALS['wgContLang']->getFormattedNsText( NS_TALK ) ) );
		if( $title->exists() )
			$tools[] = $skin->makeKnownLinkObj( $title, wfMsgHtml( 'history_short' ), 'action=history' );
		return '<li>'
			. Xml::check( 'titles[]', false, array( 'value' => $title->getPrefixedText() ) )
			. $link . ' (' . implode( ' | ', $tools ) . ')' . '</li>';
		}
	
	/**
	 * Show a form for editing the watchlist in "raw" mode
	 *
	 * @param OutputPage $output
	 * @param User $user
	 */
	public function showRawForm( $output, $user ) {
		$this->showItemCount( $output, $user );
		$self = SpecialPage::getTitleFor( 'Watchlist' );
		$form  = Xml::openElement( 'form', array( 'method' => 'post',
			'action' => $self->getLocalUrl( 'action=raw' ) ) );
		$form .= Xml::hidden( 'token', $user->editToken( 'watchlistedit' ) );
		$form .= '<fieldset><legend>' . wfMsgHtml( 'watchlistedit-raw-legend' ) . '</legend>';
		$form .= wfMsgExt( 'watchlistedit-raw-explain', 'parse' );
		$form .= Xml::label( wfMsg( 'watchlistedit-raw-titles' ), 'titles' );
		$form .= Xml::openElement( 'textarea', array( 'id' => 'titles', 'name' => 'titles',
			'rows' => 6, 'cols' => 80 ) );
		foreach( $this->getWatchlist( $user ) as $namespace => $pages ) {
			foreach( $pages as $dbkey => $redirect ) {
				$title = Title::makeTitleSafe( $namespace, $dbkey );
				if( $title instanceof Title )
					$form .= htmlspecialchars( $title->getPrefixedText() ) . "\n";
			}
		}
		$form .= '</textarea>';
		$form .= '<p>' . Xml::submitButton( wfMsg( 'watchlistedit-raw-submit' ) ) . '</p>';
		$form .= '</fieldset></form>';
		$output->addHtml( $form );
	}
	
	/**
	 * Determine whether we are editing the watchlist, and if so, what
	 * kind of editing operation
	 *
	 * @param WebRequest $request
	 * @param mixed $par
	 * @return int
	 */
	public static function getMode( $request, $par ) {
		$mode = strtolower( $request->getVal( 'action', $par ) );
		switch( $mode ) {
			case 'clear':
				return self::EDIT_CLEAR;
			case 'raw':
				return self::EDIT_RAW;
			case 'edit':
				return self::EDIT_NORMAL;
			default:
				return false;
		}
	}

}