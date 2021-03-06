<?php
/**
 * API for MediaWiki 1.8+
 *
 * Created on June 1, 2008
 *
 * Copyright © 2008 Bryan Tong Minh <Bryan.TongMinh@Gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	// Eclipse helper - will be ignored in production
	require_once( "ApiBase.php" );
}

/**
 * API Module to facilitate sending of emails to users
 * @ingroup API
 */
class ApiEmailUser extends ApiBase {

	public function __construct( $main, $action ) {
		parent::__construct( $main, $action );
	}

	public function execute() {
		global $wgUser;

		$params = $this->extractRequestParams();

		// Validate target
		$targetUser = SpecialEmailUser::getTarget( $params['target'] );
		if ( !( $targetUser instanceof User ) ) {
			$this->dieUsageMsg( array( $targetUser ) );
		}

		// Check permissions and errors
		$error = SpecialEmailUser::getPermissionsError( $wgUser, $params['token'] );
		if ( $error ) {
			$this->dieUsageMsg( array( $error ) );
		}

		$data = array(
			'Target' => $targetUser->getName(),
			'Text' => $params['text'],
			'Subject' => $params['subject'],
			'CCMe' => $params['ccme'],
		);
		$retval = SpecialEmailUser::submit( $data );
		if ( $retval === true ) {
			$result = array( 'result' => 'Success' );
		} else {
			$result = array(
				'result' => 'Failure',
				'message' => $retval
			);
		}

		$this->getResult()->addValue( null, $this->getModuleName(), $result );
	}

	public function mustBePosted() {
		return true;
	}

	public function isWriteMode() {
		return true;
	}

	public function getAllowedParams() {
		return array(
			'target' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'subject' => null,
			'text' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'token' => null,
			'ccme' => false,
		);
	}

	public function getParamDescription() {
		return array(
			'target' => 'User to send email to',
			'subject' => 'Subject header',
			'text' => 'Mail body',
			'token' => 'A token previously acquired via prop=info',
			'ccme' => 'Send a copy of this mail to me',
		);
	}

	public function getDescription() {
		return 'Email a user.';
	}

	public function getPossibleErrors() {
		return array_merge( parent::getPossibleErrors(), array(
			array( 'usermaildisabled' ),
		) );
	}

	public function needsToken() {
		return true;
	}

	public function getTokenSalt() {
		return '';
	}

	protected function getExamples() {
		return array(
			'api.php?action=emailuser&target=WikiSysop&text=Content'
		);
	}

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}
}
