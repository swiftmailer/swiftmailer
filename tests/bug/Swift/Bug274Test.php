<?php
require_once 'Swift/Tests/SwiftUnitTestCase.php';
class Swift_Bug274Test extends Swift_Tests_SwiftUnitTestCase
{
    public function testEmptyFileNameAsAttachement()
    {
        $message = new Swift_Message();
        $this->expectException(new Swift_IoException('File Name is empty'));
        $message->attach(Swift_Attachment::fromPath(''));
	}
    public function testNonEmptyFileNameAsAttachement()
    {
        $message = new Swift_Message();
        try {
        	$message->attach(Swift_Attachment::fromPath(__FILE__));
        } catch (Exception $e) {
        	$this->exception($e);
        }
	}
}
