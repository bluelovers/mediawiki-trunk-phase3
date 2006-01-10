<?php
/**
 * Main wiki script; see docs/design.txt
 * @package MediaWiki
 */
$wgRequestTime = microtime();

# getrusage() does not exist on the Window$ platform, catching this
if ( function_exists ( 'getrusage' ) ) {
	$wgRUstart = getrusage();
} else {
	$wgRUstart = array() ;
}

unset( $IP );
@ini_set( 'allow_url_fopen', 0 ); # For security...

if ( isset( $_REQUEST['GLOBALS'] ) ) {
	die( '<a href="http://www.hardened-php.net/index.76.html">$GLOBALS overwrite vulnerability</a>');
}

# Valid web server entry point, enable includes.
# Please don't move this line to includes/Defines.php. This line essentially defines
# a valid entry point. If you put it in includes/Defines.php, then any script that includes
# it becomes an entry point, thereby defeating its purpose.
define( 'MEDIAWIKI', true );
require_once( './includes/Defines.php' );

if( !file_exists( 'LocalSettings.php' ) ) {
	$IP = "." ;
	require_once( 'includes/DefaultSettings.php' ); # used for printing the version
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
	<head>
		<title>MediaWiki <?php echo $wgVersion ?></title>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
		<style type='text/css' media='screen, projection'>
			html, body {
				color: #000;
				background-color: #fff;
				font-family: sans-serif;
				text-align: center;
			}

			h1 {
				font-size: 150%;
			}
		</style>
	</head>
	<body>
		<img src='skins/common/images/mediawiki.png' alt='The MediaWiki logo' />

		<h1>MediaWiki <?php echo $wgVersion ?></h1>
		<div class='error'>
		<?php
		if ( file_exists( 'config/LocalSettings.php' ) ) {
			echo( "To complete the installation, move <tt>config/LocalSettings.php</tt> to the parent directory." );
		} else {
			echo( "Please <a href='config/index.php' title='setup'>setup the wiki</a> first." );
		}
		?>

		</div>
	</body>
</html>
<?php
	die();
}

require_once( './LocalSettings.php' );
require_once( 'includes/Setup.php' );

wfProfileIn( 'main-misc-setup' );
OutputPage::setEncodings(); # Not really used yet

# Query string fields
$action = $wgRequest->getVal( 'action', 'view' );
$title = $wgRequest->getVal( 'title' );

if ($wgRequest->getVal( 'printable' ) == 'yes') {
	$wgOut->setPrintable();
}

if ( '' == $title && 'delete' != $action ) {
	$wgTitle = Title::newFromText( wfMsgForContent( 'mainpage' ) );
} elseif ( $curid = $wgRequest->getInt( 'curid' ) ) {
	# URLs like this are generated by RC, because rc_title isn't always accurate
	$wgTitle = Title::newFromID( $curid );
} else {
	$wgTitle = Title::newFromURL( $title );
	/* check variant links so that interwiki links don't have to worry about
	   the possible different language variants
	*/
	if( count($wgContLang->getVariants()) > 1 && !is_null($wgTitle) && $wgTitle->getArticleID() == 0 )
		$wgContLang->findVariantLink( $title, $wgTitle );

}
wfProfileOut( 'main-misc-setup' );

# Debug statement for user levels
// print_r($wgUser);

$search = $wgRequest->getText( 'search' );
if( !is_null( $search ) && $search !== '' ) {
	// Compatibility with old search URLs which didn't use Special:Search
	// Do this above the read whitelist check for security...
	$wgTitle = Title::makeTitle( NS_SPECIAL, 'Search' );
}

# If the user is not logged in, the Namespace:title of the article must be in
# the Read array in order for the user to see it. (We have to check here to
# catch special pages etc. We check again in Article::view())
if ( !is_null( $wgTitle ) && !$wgTitle->userCanRead() ) {
	$wgOut->loginToUse();
	$wgOut->output();
	exit;
}

wfProfileIn( 'main-action' );

if( !$wgDisableInternalSearch && !is_null( $search ) && $search !== '' ) {
	require_once( 'includes/SpecialSearch.php' );
	$wgTitle = Title::makeTitle( NS_SPECIAL, 'Search' );
	wfSpecialSearch();
} else if( !$wgTitle or $wgTitle->getDBkey() == '' ) {
	$wgTitle = Title::newFromText( wfMsgForContent( 'badtitle' ) );
	$wgOut->errorpage( 'badtitle', 'badtitletext' );
} else if ( $wgTitle->getInterwiki() != '' ) {
	if( $rdfrom = $wgRequest->getVal( 'rdfrom' ) ) {
		$url = $wgTitle->getFullURL( 'rdfrom=' . urlencode( $rdfrom ) );
	} else {
		$url = $wgTitle->getFullURL();
	}
	# Check for a redirect loop
	if ( !preg_match( '/^' . preg_quote( $wgServer, '/' ) . '/', $url ) && $wgTitle->isLocal() ) {
		$wgOut->redirect( $url );
	} else {
		$wgTitle = Title::newFromText( wfMsgForContent( 'badtitle' ) );
		$wgOut->errorpage( 'badtitle', 'badtitletext' );
	}
} else if ( ( $action == 'view' ) &&
	(!isset( $_GET['title'] ) || $wgTitle->getPrefixedDBKey() != $_GET['title'] ) &&
	!count( array_diff( array_keys( $_GET ), array( 'action', 'title' ) ) ) )
{
	/* redirect to canonical url, make it a 301 to allow caching */
	$wgOut->setSquidMaxage( 1200 );
	$wgOut->redirect( $wgTitle->getFullURL(), '301');
} else if ( NS_SPECIAL == $wgTitle->getNamespace() ) {
	# actions that need to be made when we have a special pages
	SpecialPage::executePath( $wgTitle );
} else {
	if ( NS_MEDIA == $wgTitle->getNamespace() ) {
		$wgTitle = Title::makeTitle( NS_IMAGE, $wgTitle->getDBkey() );
	}

	$ns = $wgTitle->getNamespace();

	// Namespace might change when using redirects
	if($action == 'view' && !$wgRequest->getVal( 'oldid' ) ) {
		$wgArticle = new Article( $wgTitle );
		$rTitle = Title::newFromRedirect( $wgArticle->fetchContent() );
		if($rTitle) {
			# Reload from the page pointed to later
			$wgArticle->mContentLoaded = false;
			$ns = $rTitle->getNamespace();
		}
	}


	require_once ( "includes/Wiki.php" ) ;
	$mediaWiki = new MediaWiki() ;

	$wgArticle =& $mediaWiki->setCorrectArticleClass ( $wgArticle , $wgTitle , $ns ) ;

	if ( in_array( $action, $wgDisabledActions ) ) {
		$wgOut->errorpage( 'nosuchaction', 'nosuchactiontext' );
	} else {
		$mediaWiki->setVal ( "SquidMaxage" , $wgSquidMaxage ) ;
		$mediaWiki->setVal ( "EnableDublinCoreRdf" , $wgEnableDublinCoreRdf ) ;
		$mediaWiki->setVal ( "EnableCreativeCommonsRdf" , $wgEnableCreativeCommonsRdf ) ;
		$mediaWiki->setVal ( "CommandLineMode" , $wgCommandLineMode ) ;
		$mediaWiki->setVal ( "UseExternalEditor" , $wgUseExternalEditor ) ;
		$mediaWiki->performAction ( $action , $wgOut , $wgArticle , $wgTitle , $wgUser , $wgRequest ) ;
	}


}
wfProfileOut( 'main-action' );

# Deferred updates aren't really deferred anymore. It's important to report errors to the
# user, and that means doing this before OutputPage::output(). Note that for page saves,
# the client will wait until the script exits anyway before following the redirect.
wfProfileIn( 'main-updates' );
foreach ( $wgDeferredUpdateList as $up ) {
	$up->doUpdate();
}
wfProfileOut( 'main-updates' );

wfProfileIn( 'main-cleanup' );
$wgLoadBalancer->saveMasterPos();

# Now commit any transactions, so that unreported errors after output() don't roll back the whole thing
$wgLoadBalancer->commitAll();

$wgOut->output();

foreach ( $wgPostCommitUpdateList as $up ) {
	$up->doUpdate();
}

wfProfileOut( 'main-cleanup' );

wfProfileClose();
logProfilingData();
$wgLoadBalancer->closeAll();
wfDebug( "Request ended normally\n" );
?>
