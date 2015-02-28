<?php

class Swift_Plugins_DefaultSenderPluginTest extends \PHPUnit_Framework_TestCase
{

    public function testPluginChangesSenderEmail()
    {
        $message = Swift_Message::newInstance()
            ->setSubject('...')
            ->setBody('...')
        ;

        $plugin = new Swift_Plugins_DefaultSenderPlugin('john@example.com');

        $evt = $this->_createSendEvent($message);

        $this->assertEquals($message->getFrom(), array());

        $plugin->beforeSendPerformed($evt);

        $this->assertEquals($message->getFrom(), array('john@example.com' => ''));

        $plugin->sendPerformed($evt);

        $this->assertEquals($message->getFrom(), array());
    }

    public function testPluginChangesSenderEmailAndName()
    {
        $message = Swift_Message::newInstance()
            ->setSubject('...')
            ->setBody('...')
        ;

        $plugin = new Swift_Plugins_DefaultSenderPlugin('john@example.com', 'John Doe');

        $evt = $this->_createSendEvent($message);

        $this->assertEquals($message->getFrom(), array());

        $plugin->beforeSendPerformed($evt);

        $this->assertEquals($message->getFrom(), array('john@example.com' => 'John Doe'));

        $plugin->sendPerformed($evt);

        $this->assertEquals($message->getFrom(), array());
    }

    // -- Creation Methods

    private function _createSendEvent(Swift_Mime_Message $message)
    {
        $evt = $this->getMockBuilder('Swift_Events_SendEvent')
                    ->disableOriginalConstructor()
                    ->getMock();
        $evt->expects($this->any())
            ->method('getMessage')
            ->will($this->returnValue($message));

        return $evt;
    }
}
