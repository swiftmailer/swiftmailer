<?php

require_once 'Swift/Transport/AbstractSmtpEventSupportTest.php';
require_once 'Swift/Transport/SendmailTransport.php';
require_once 'Swift/Mime/Message.php';
require_once 'Swift/Events/EventDispatcher.php';

class Swift_Transport_SendmailTransportTest
    extends Swift_Transport_AbstractSmtpEventSupportTest
{
    protected function _getTransport($buf, $dispatcher = null, $command = '/usr/sbin/sendmail -bs')
    {
        if (!$dispatcher) {
            $dispatcher = $this->_createEventDispatcher();
        }
        $transport = new Swift_Transport_SendmailTransport($buf, $dispatcher);
        $transport->setCommand($command);
        return $transport;
    }

    protected function _getSendmail($buf, $dispatcher = null)
    {
        if (!$dispatcher) {
            $dispatcher = $this->_createEventDispatcher();
        }
        $sendmail = new Swift_Transport_SendmailTransport($buf, $dispatcher);
        return $sendmail;
    }

    public function testCommandCanBeSetAndFetched()
    {
        $buf = $this->_getBuffer();
        $sendmail = $this->_getSendmail($buf);

        $sendmail->setCommand('/usr/sbin/sendmail -bs');
        $this->assertEqual('/usr/sbin/sendmail -bs', $sendmail->getCommand());
        $sendmail->setCommand('/usr/sbin/sendmail -oi -t');
        $this->assertEqual('/usr/sbin/sendmail -oi -t', $sendmail->getCommand());
    }

    public function testSendingMessageIn_t_ModeUsesSimplePipe()
    {
        $buf = $this->_getBuffer();
        $sendmail = $this->_getSendmail($buf);
        $message = $this->_createMessage();

        $this->_checking(Expectations::create()
            -> allowing($message)->getTo() -> returns(array('foo@bar'=>'Foobar', 'zip@button'=>'Zippy'))
            -> one($message)->toByteStream($buf)
            -> ignoring($message)
            -> one($buf)->initialize()
            -> one($buf)->terminate()
            -> one($buf)->setWriteTranslations(array("\r\n"=>"\n", "\n." => "\n.."))
            -> one($buf)->setWriteTranslations(array())
            -> ignoring($buf)
            );

        $sendmail->setCommand('/usr/sbin/sendmail -t');
        $this->assertEqual(2, $sendmail->send($message));
    }

    public function testSendingIn_t_ModeWith_i_FlagDoesntEscapeDot()
    {
        $buf = $this->_getBuffer();
        $sendmail = $this->_getSendmail($buf);
        $message = $this->_createMessage();

        $this->_checking(Expectations::create()
            -> allowing($message)->getTo() -> returns(array('foo@bar'=>'Foobar', 'zip@button'=>'Zippy'))
            -> one($message)->toByteStream($buf)
            -> ignoring($message)
            -> one($buf)->initialize()
            -> one($buf)->terminate()
            -> one($buf)->setWriteTranslations(array("\r\n"=>"\n"))
            -> one($buf)->setWriteTranslations(array())
            -> ignoring($buf)
            );

        $sendmail->setCommand('/usr/sbin/sendmail -i -t');
        $this->assertEqual(2, $sendmail->send($message));
    }

    public function testSendingInTModeWith_oi_FlagDoesntEscapeDot()
    {
        $buf = $this->_getBuffer();
        $sendmail = $this->_getSendmail($buf);
        $message = $this->_createMessage();

        $this->_checking(Expectations::create()
            -> allowing($message)->getTo() -> returns(array('foo@bar'=>'Foobar', 'zip@button'=>'Zippy'))
            -> one($message)->toByteStream($buf)
            -> ignoring($message)
            -> one($buf)->initialize()
            -> one($buf)->terminate()
            -> one($buf)->setWriteTranslations(array("\r\n"=>"\n"))
            -> one($buf)->setWriteTranslations(array())
            -> ignoring($buf)
            );

        $sendmail->setCommand('/usr/sbin/sendmail -oi -t');
        $this->assertEqual(2, $sendmail->send($message));
    }

    public function testSendingMessageRegeneratesId()
    {
        $buf = $this->_getBuffer();
        $sendmail = $this->_getSendmail($buf);
        $message = $this->_createMessage();

        $this->_checking(Expectations::create()
            -> allowing($message)->getTo() -> returns(array('foo@bar'=>'Foobar', 'zip@button'=>'Zippy'))
            -> one($message)->generateId()
            -> ignoring($message)
            -> one($buf)->initialize()
            -> one($buf)->terminate()
            -> one($buf)->setWriteTranslations(array("\r\n"=>"\n", "\n." => "\n.."))
            -> one($buf)->setWriteTranslations(array())
            -> ignoring($buf)
            );

        $sendmail->setCommand('/usr/sbin/sendmail -t');
        $this->assertEqual(2, $sendmail->send($message));
    }

    public function testFluidInterface()
    {
        $buf = $this->_getBuffer();
        $sendmail = $this->_getTransport($buf);

        $ref = $sendmail->setCommand('/foo');
        $this->assertReference($ref, $sendmail);
    }
}
