<?php

// TODO update test

class Swift_Bug274Test extends \PHPUnit\Framework\TestCase
{
    public function testEmptyFileNameAsAttachment()
    {
        $message = new Swift_Message();
        // TODO no longer supported by phpunit test must be modified
        // $this->setExpectedException('Swift_IoException', 'The path cannot be empty');
        try {
            $message->attach(Swift_Attachment::fromPath(''));
        } catch (Exception $e) {
            if (!is_a($e, 'Swift_IoException')) {
                $this->fail('Expected Swift_IoException - The path cannot be empty');
            }
        }
    }

    public function testNonEmptyFileNameAsAttachment()
    {
        $message = new Swift_Message();
        try {
            $message->attach(Swift_Attachment::fromPath(__FILE__));
        } catch (Exception $e) {
            $this->fail('Path should not be empty');
        }
    }
}
