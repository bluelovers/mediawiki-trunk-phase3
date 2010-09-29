<?php

abstract class ApiTestSetup extends PHPUnit_Framework_TestCase {
	protected static $userName;
	protected static $passWord;
	protected static $user;
	protected static $apiUrl;

	function setUp() {
		global $wgServer, $wgContLang, $wgAuth, $wgMemc, $wgRequest;

		self::$apiUrl = $wgServer . wfScript( 'api' );

		$wgMemc = new FakeMemCachedClient;
		$wgContLang = Language::factory( 'en' );
		$wgAuth = new StubObject( 'wgAuth', 'AuthPlugin' );
		$wgRequest = new FauxRequest( array() );
		self::setupUser();
	}

	static function setupUser() {
		if ( self::$user == NULL ) {
			self::$userName = "Useruser";
			self::$passWord = 'Passpass';

			self::$user = User::newFromName( self::$userName );
			if ( !self::$user->getID() ) {
				self::$user = User::createNew( self::$userName, array(
					"email" => "test@example.com",
					"real_name" => "Test User" ) );
			}
			self::$user->setPassword( self::$passWord );
			self::$user->saveSettings();
		}
		
		$GLOBALS['wgUser'] = self::$user;
	}

	function tearDown() {
		global $wgMemc;
		$wgMemc = null;
	}
}
