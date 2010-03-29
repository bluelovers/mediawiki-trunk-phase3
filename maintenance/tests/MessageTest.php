<?php

class MessageTest extends PHPUnit_Framework_TestCase {
	function testExists() {
		$this->assertTrue( Message::key( 'mainpage' )->exists() );
		$this->assertTrue( Message::key( 'mainpage' )->params( array() )->exists() );
		$this->assertTrue( Message::key( 'mainpage' )->rawParams( 'foo', 123 )->exists() );
		$this->assertFalse( Message::key( 'i-dont-exist-evar' )->exists() );
		$this->assertFalse( Message::key( 'i-dont-exist-evar' )->params( array() )->exists() );
		$this->assertFalse( Message::key( 'i-dont-exist-evar' )->rawParams( 'foo', 123 )->exists() );
	}

	function testKey() {
		$this->assertType( 'Message', Message::key( 'mainpage' ) );
		$this->assertType( 'Message', Message::key( 'i-dont-exist-evar' ) );
		$this->assertEquals( 'Main Page', Message::key( 'mainpage' )->text() );
		$this->assertEquals( '&lt;i-dont-exist-evar&gt;', Message::key( 'i-dont-exist-evar' )->text() );
	}
}