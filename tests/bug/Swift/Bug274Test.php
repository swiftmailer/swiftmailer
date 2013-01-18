<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';

class Swift_Bug274Test extends Swift_Tests_SwiftUnitTestCase
{
	public function testEmptyFileNameAsAttachement()
	{
       	$message = new Swift_Message();
		try {
			$message->attach(Swift_Attachment::fromPath(''));
		} catch (Swift_IoException $e) {
			return true;
		}
		$this->assertFalse(true, 'Exception Not Thrown');
	}
}
