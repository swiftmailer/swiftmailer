<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';

class Swift_Plugins_DecoratorPluginTest extends Swift_Tests_SwiftUnitTestCase
{
    public function testMessageBodyReceivesReplacements()
    {
        $message = $this->_createMessage(
            $this->_createHeaders(),
            array('zip@button.tld' => 'Zipathon'),
            array('chris.corbyn@swiftmailer.org' => 'Chris'),
            'Subject',
            'Hello {name}, you are customer #{id}'
            );
        $this->_checking(Expectations::create()
            -> one($message)->setBody('Hello Zip, you are customer #456')
            -> ignoring($message)
            );

        $plugin = $this->_createPlugin(
            array('zip@button.tld' => array('{name}' => 'Zip', '{id}' => '456'))
            );

        $evt = $this->_createSendEvent($message);

        $plugin->beforeSendPerformed($evt);
        $plugin->sendPerformed($evt);
    }

    public function testReplacementsCanBeAppliedToSameMessageMultipleTimes()
    {
        $message = $this->_createMessage(
            $this->_createHeaders(),
            array('zip@button.tld' => 'Zipathon', 'foo@bar.tld' => 'Foo'),
            array('chris.corbyn@swiftmailer.org' => 'Chris'),
            'Subject',
            'Hello {name}, you are customer #{id}'
            );
        $this->_checking(Expectations::create()
            -> one($message)->setBody('Hello Zip, you are customer #456')
            -> one($message)->setBody('Hello {name}, you are customer #{id}')
            -> one($message)->setBody('Hello Foo, you are customer #123')
            -> ignoring($message)
            );

        $plugin = $this->_createPlugin(
            array(
                'foo@bar.tld' => array('{name}' => 'Foo', '{id}' => '123'),
                'zip@button.tld' => array('{name}' => 'Zip', '{id}' => '456')
                )
            );

        $evt = $this->_createSendEvent($message);

        $plugin->beforeSendPerformed($evt);
        $plugin->sendPerformed($evt);
        $plugin->beforeSendPerformed($evt);
        $plugin->sendPerformed($evt);
    }

    public function testReplacementsCanBeMadeInHeaders()
    {
        $headers = $this->_createHeaders(array(
            $returnPathHeader = $this->_createHeader('Return-Path', 'foo-{id}@swiftmailer.org'),
            $toHeader = $this->_createHeader('Subject', 'A message for {name}!')
        ));

        $message = $this->_createMessage(
            $headers,
            array('zip@button.tld' => 'Zipathon'),
            array('chris.corbyn@swiftmailer.org' => 'Chris'),
            'A message for {name}!',
            'Hello {name}, you are customer #{id}'
            );
        $this->_checking(Expectations::create()
            -> one($message)->setBody('Hello Zip, you are customer #456')
            -> one($toHeader)->setFieldBodyModel('A message for Zip!')
            -> one($returnPathHeader)->setFieldBodyModel('foo-456@swiftmailer.org')
            -> ignoring($message)
            -> ignoring($toHeader)
            -> ignoring($returnPathHeader)
            );

        $plugin = $this->_createPlugin(
            array('zip@button.tld' => array('{name}' => 'Zip', '{id}' => '456'))
            );

        $evt = $this->_createSendEvent($message);

        $plugin->beforeSendPerformed($evt);
        $plugin->sendPerformed($evt);
    }

    public function testReplacementsAreMadeOnSubparts()
    {
        $part1 = $this->_createPart('text/plain', 'Your name is {name}?', '1@x');
        $part2 = $this->_createPart('text/html', 'Your <em>name</em> is {name}?', '2@x');
        $message = $this->_createMessage(
            $this->_createHeaders(),
            array('zip@button.tld' => 'Zipathon'),
            array('chris.corbyn@swiftmailer.org' => 'Chris'),
            'A message for {name}!',
            'Subject'
            );
        $this->_checking(Expectations::create()
            -> ignoring($message)->getChildren() -> returns(array($part1, $part2))
            -> one($part1)->setBody('Your name is Zip?')
            -> one($part2)->setBody('Your <em>name</em> is Zip?')
            -> ignoring($part1)
            -> ignoring($part2)
            -> ignoring($message)
            );

        $plugin = $this->_createPlugin(
            array('zip@button.tld' => array('{name}' => 'Zip', '{id}' => '456'))
            );

        $evt = $this->_createSendEvent($message);

        $plugin->beforeSendPerformed($evt);
        $plugin->sendPerformed($evt);
    }

    public function testReplacementsCanBeTakenFromCustomReplacementsObject()
    {
        $message = $this->_createMessage(
            $this->_createHeaders(),
            array('foo@bar' => 'Foobar', 'zip@zap' => 'Zip zap'),
            array('chris.corbyn@swiftmailer.org' => 'Chris'),
            'Subject',
            'Something {a}'
            );

        $replacements = $this->_createReplacements();

        $this->_checking(Expectations::create()
            -> one($message)->setBody('Something b')
            -> one($message)->setBody('Something c')
            -> one($replacements)->getReplacementsFor('foo@bar') -> returns(array('{a}'=>'b'))
            -> one($replacements)->getReplacementsFor('zip@zap') -> returns(array('{a}'=>'c'))
            -> ignoring($message)
            );

        $plugin = $this->_createPlugin($replacements);

        $evt = $this->_createSendEvent($message);

        $plugin->beforeSendPerformed($evt);
        $plugin->sendPerformed($evt);
        $plugin->beforeSendPerformed($evt);
        $plugin->sendPerformed($evt);
    }

    // -- Creation methods

    private function _createMessage($headers, $to = array(), $from = null, $subject = null,
        $body = null)
    {
        $message = $this->_mock('Swift_Mime_Message');
        foreach ($to as $addr => $name) {
            $this->_checking(Expectations::create()
                -> one($message)->getTo() -> returns(array($addr => $name))
                );
        }
        $this->_checking(Expectations::create()
            -> allowing($message)->getHeaders() -> returns($headers)
            -> ignoring($message)->getFrom() -> returns($from)
            -> ignoring($message)->getSubject() -> returns($subject)
            -> ignoring($message)->getBody() -> returns($body)
            );
        return $message;
    }

    private function _createPlugin($replacements)
    {
        return new Swift_Plugins_DecoratorPlugin($replacements);
    }

    private function _createReplacements()
    {
        return $this->_mock('Swift_Plugins_Decorator_Replacements');
    }

    private function _createSendEvent(Swift_Mime_Message $message)
    {
        $evt = $this->_mock('Swift_Events_SendEvent');
        $this->_checking(Expectations::create()
            -> ignoring($evt)->getMessage() -> returns($message)
            -> ignoring($evt)
            );
        return $evt;
    }

    private function _createPart($type, $body, $id)
    {
        $part = $this->_mock('Swift_Mime_MimeEntity');
        $this->_checking(Expectations::create()
            -> ignoring($part)->getContentType() -> returns($type)
            -> ignoring($part)->getBody() -> returns($body)
            -> ignoring($part)->getId() -> returns($id)
            );
        return $part;
    }

    private function _createHeaders($headers = array())
    {
        $set = $this->_mock('Swift_Mime_HeaderSet');

        $this->_checking(Expectations::create()
            -> allowing($set)->getAll() -> returns($headers)
            -> ignoring($set)
            );

        foreach ($headers as $header) {
            $set->set($header);
        }

        return $set;
    }

    private function _createHeader($name, $body = '')
    {
        $header = $this->_mock('Swift_Mime_Header');
        $this->_checking(Expectations::create()
            -> ignoring($header)->getFieldName() -> returns($name)
            -> ignoring($header)->getFieldBodyModel() -> returns($body)
            );
        return $header;
    }
}
