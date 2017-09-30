<?php

class Swift_Plugins_DecoratorPluginTest extends \SwiftMailerTestCase
{
    public function testMessageBodyReceivesReplacements()
    {
        $message = $this->createMessage(
            $this->createHeaders(),
            array('zip@button.tld' => 'Zipathon'),
            array('chris.corbyn@swiftmailer.org' => 'Chris'),
            'Subject',
            'Hello {name}, you are customer #{id}'
            );
        $message->shouldReceive('setBody')
                ->once()
                ->with('Hello Zip, you are customer #456');
        $message->shouldReceive('setBody')
                ->zeroOrMoreTimes();

        $plugin = $this->createPlugin(
            array('zip@button.tld' => array('{name}' => 'Zip', '{id}' => '456'))
            );

        $evt = $this->createSendEvent($message);

        $plugin->beforeSendPerformed($evt);
        $plugin->sendPerformed($evt);
    }

    public function testReplacementsCanBeAppliedToSameMessageMultipleTimes()
    {
        $message = $this->createMessage(
            $this->createHeaders(),
            array('zip@button.tld' => 'Zipathon', 'foo@bar.tld' => 'Foo'),
            array('chris.corbyn@swiftmailer.org' => 'Chris'),
            'Subject',
            'Hello {name}, you are customer #{id}'
            );
        $message->shouldReceive('setBody')
                ->once()
                ->with('Hello Zip, you are customer #456');
        $message->shouldReceive('setBody')
                ->once()
                ->with('Hello {name}, you are customer #{id}');
        $message->shouldReceive('setBody')
                ->once()
                ->with('Hello Foo, you are customer #123');
        $message->shouldReceive('setBody')
                ->zeroOrMoreTimes();

        $plugin = $this->createPlugin(
            array(
                'foo@bar.tld' => array('{name}' => 'Foo', '{id}' => '123'),
                'zip@button.tld' => array('{name}' => 'Zip', '{id}' => '456'),
                )
            );

        $evt = $this->createSendEvent($message);

        $plugin->beforeSendPerformed($evt);
        $plugin->sendPerformed($evt);
        $plugin->beforeSendPerformed($evt);
        $plugin->sendPerformed($evt);
    }

    public function testReplacementsCanBeMadeInHeaders()
    {
        $headers = $this->createHeaders(array(
            $returnPathHeader = $this->createHeader('Return-Path', 'foo-{id}@swiftmailer.org'),
            $toHeader = $this->createHeader('Subject', 'A message for {name}!'),
        ));

        $message = $this->createMessage(
            $headers,
            array('zip@button.tld' => 'Zipathon'),
            array('chris.corbyn@swiftmailer.org' => 'Chris'),
            'A message for {name}!',
            'Hello {name}, you are customer #{id}'
            );

        $message->shouldReceive('setBody')
                ->once()
                ->with('Hello Zip, you are customer #456');
        $toHeader->shouldReceive('setFieldBodyModel')
                 ->once()
                 ->with('A message for Zip!');
        $returnPathHeader->shouldReceive('setFieldBodyModel')
                         ->once()
                         ->with('foo-456@swiftmailer.org');
        $message->shouldReceive('setBody')
                ->zeroOrMoreTimes();
        $toHeader->shouldReceive('setFieldBodyModel')
                 ->zeroOrMoreTimes();
        $returnPathHeader->shouldReceive('setFieldBodyModel')
                         ->zeroOrMoreTimes();

        $plugin = $this->createPlugin(
            array('zip@button.tld' => array('{name}' => 'Zip', '{id}' => '456'))
            );
        $evt = $this->createSendEvent($message);

        $plugin->beforeSendPerformed($evt);
        $plugin->sendPerformed($evt);
    }

    public function testReplacementsAreMadeOnSubparts()
    {
        $part1 = $this->createPart('text/plain', 'Your name is {name}?', '1@x');
        $part2 = $this->createPart('text/html', 'Your <em>name</em> is {name}?', '2@x');
        $message = $this->createMessage(
            $this->createHeaders(),
            array('zip@button.tld' => 'Zipathon'),
            array('chris.corbyn@swiftmailer.org' => 'Chris'),
            'A message for {name}!',
            'Subject'
            );
        $message->shouldReceive('getChildren')
                ->zeroOrMoreTimes()
                ->andReturn(array($part1, $part2));
        $part1->shouldReceive('setBody')
              ->once()
              ->with('Your name is Zip?');
        $part2->shouldReceive('setBody')
              ->once()
              ->with('Your <em>name</em> is Zip?');
        $part1->shouldReceive('setBody')
              ->zeroOrMoreTimes();
        $part2->shouldReceive('setBody')
              ->zeroOrMoreTimes();

        $plugin = $this->createPlugin(
            array('zip@button.tld' => array('{name}' => 'Zip', '{id}' => '456'))
            );

        $evt = $this->createSendEvent($message);

        $plugin->beforeSendPerformed($evt);
        $plugin->sendPerformed($evt);
    }

    public function testReplacementsCanBeTakenFromCustomReplacementsObject()
    {
        $message = $this->createMessage(
            $this->createHeaders(),
            array('foo@bar' => 'Foobar', 'zip@zap' => 'Zip zap'),
            array('chris.corbyn@swiftmailer.org' => 'Chris'),
            'Subject',
            'Something {a}'
            );

        $replacements = $this->createReplacements();

        $message->shouldReceive('setBody')
                ->once()
                ->with('Something b');
        $message->shouldReceive('setBody')
                ->once()
                ->with('Something c');
        $message->shouldReceive('setBody')
                ->zeroOrMoreTimes();
        $replacements->shouldReceive('getReplacementsFor')
                     ->once()
                     ->with('foo@bar')
                     ->andReturn(array('{a}' => 'b'));
        $replacements->shouldReceive('getReplacementsFor')
                     ->once()
                     ->with('zip@zap')
                     ->andReturn(array('{a}' => 'c'));

        $plugin = $this->createPlugin($replacements);

        $evt = $this->createSendEvent($message);

        $plugin->beforeSendPerformed($evt);
        $plugin->sendPerformed($evt);
        $plugin->beforeSendPerformed($evt);
        $plugin->sendPerformed($evt);
    }

    public function testReplacementsWithAMessageWithImmutableDate()
    {
        $message = (new Swift_Message('subject foo'))
            ->setBody('body foo')
            ->addTo('somebody@hostname.tld')
            ->addFrom('somebody@hostname.tld');

        $evt = $this->createSendEvent($message);

        $plugin = $this->createPlugin(array('somebody@hostname.tld' => array('foo' => 'bar')));

        $plugin->beforeSendPerformed($evt);

        $this->assertEquals('subject bar', $message->getSubject());
        $this->assertEquals('body bar', $message->getBody());
    }

    private function createMessage($headers, $to = array(), $from = null, $subject = null,
        $body = null)
    {
        $message = $this->getMockery('Swift_Mime_SimpleMessage')->shouldIgnoreMissing();
        foreach ($to as $addr => $name) {
            $message->shouldReceive('getTo')
                    ->once()
                    ->andReturn(array($addr => $name));
        }
        $message->shouldReceive('getHeaders')
                ->zeroOrMoreTimes()
                ->andReturn($headers);
        $message->shouldReceive('getFrom')
                ->zeroOrMoreTimes()
                ->andReturn($from);
        $message->shouldReceive('getSubject')
                ->zeroOrMoreTimes()
                ->andReturn($subject);
        $message->shouldReceive('getBody')
                ->zeroOrMoreTimes()
                ->andReturn($body);

        return $message;
    }

    private function createPlugin($replacements)
    {
        return new Swift_Plugins_DecoratorPlugin($replacements);
    }

    private function createReplacements()
    {
        return $this->getMockery('Swift_Plugins_Decorator_Replacements')->shouldIgnoreMissing();
    }

    private function createSendEvent(Swift_Mime_SimpleMessage $message)
    {
        $evt = $this->getMockery('Swift_Events_SendEvent')->shouldIgnoreMissing();
        $evt->shouldReceive('getMessage')
            ->zeroOrMoreTimes()
            ->andReturn($message);

        return $evt;
    }

    private function createPart($type, $body, $id)
    {
        $part = $this->getMockery('Swift_Mime_SimpleMimeEntity')->shouldIgnoreMissing();
        $part->shouldReceive('getContentType')
             ->zeroOrMoreTimes()
             ->andReturn($type);
        $part->shouldReceive('getBody')
             ->zeroOrMoreTimes()
             ->andReturn($body);
        $part->shouldReceive('getId')
             ->zeroOrMoreTimes()
             ->andReturn($id);

        return $part;
    }

    private function createHeaders($headers = array())
    {
        $set = $this->getMockery('Swift_Mime_SimpleHeaderSet')->shouldIgnoreMissing();
        $set->shouldReceive('getAll')
            ->zeroOrMoreTimes()
            ->andReturn($headers);

        foreach ($headers as $header) {
            $set->set($header);
        }

        return $set;
    }

    private function createHeader($name, $body = '')
    {
        $header = $this->getMockery('Swift_Mime_Header')->shouldIgnoreMissing();
        $header->shouldReceive('getFieldName')
               ->zeroOrMoreTimes()
               ->andReturn($name);
        $header->shouldReceive('getFieldBodyModel')
               ->zeroOrMoreTimes()
               ->andReturn($body);

        return $header;
    }
}
