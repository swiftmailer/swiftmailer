<?php

class Swift_Bug274Test extends \PHPUnit\Framework\TestCase
{
    public function testEmptyFileNameAsAttachment()
    {
        $this->expectException(\Swift_IoException::class);
        $this->expectExceptionMessage('The path cannot be empty');

        $message = new Swift_Message();
        $message->attach(Swift_Attachment::fromPath(''));
    }

    public function testNonEmptyFileNameAsAttachment()
    {
        $message = new Swift_Message();
        try {
            $message->attach(Swift_Attachment::fromPath(__FILE__));
        } catch (Exception $e) {
            $this->fail('Path should not be empty');
        }
        $this->addToAssertionCount(1);
    }
}
