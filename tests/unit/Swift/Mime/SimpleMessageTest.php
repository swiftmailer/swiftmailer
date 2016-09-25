<?php

class Swift_Mime_SimpleMessageTest extends Swift_Mime_MimePartTest
{
    public function testNestingLevelIsSubpart()
    {
        //Overridden
    }

    public function testNestingLevelIsTop()
    {
        $message = $this->_createMessage($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertEquals(
            Swift_Mime_MimeEntity::LEVEL_TOP, $message->getNestingLevel()
            );
    }

    public function testDateIsReturnedFromHeader()
    {
        $date = $this->_createHeader('Date', 123);
        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Date' => $date)),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertEquals(123, $message->getDate());
    }

    public function testDataIsNullWhenNotSet()
    {
        $message = $this->createMessageWithEmptyHeaderSet();

        $this->assertNull($message->getDate());
    }

    public function testDateIsSetInHeader()
    {
        $date = $this->_createHeader('Date', 123, array(), false);
        $date->shouldReceive('setFieldBodyModel')
             ->once()
             ->with(1234);
        $date->shouldReceive('setFieldBodyModel')
             ->zeroOrMoreTimes();

        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Date' => $date)),
            $this->_createEncoder(), $this->_createCache()
            );
        $message->setDate(1234);
    }

    public function testDateHeaderIsCreatedIfNonePresent()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('addDateHeader')
                ->once()
                ->with('Date', 1234);
        $headers->shouldReceive('addDateHeader')
                ->zeroOrMoreTimes();

        $message = $this->_createMessage($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $message->setDate(1234);
    }

    public function testDateHeaderIsAddedDuringConstruction()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('addDateHeader')
                ->once()
                ->with('Date', '/^[0-9]+$/D');

        $message = $this->_createMessage($headers, $this->_createEncoder(),
            $this->_createCache()
            );
    }

    public function testIdIsReturnedFromHeader()
    {
        /* -- RFC 2045, 7.
        In constructing a high-level user agent, it may be desirable to allow
        one body to make reference to another.  Accordingly, bodies may be
        labelled using the "Content-ID" header field, which is syntactically
        identical to the "Message-ID" header field
        */

        $messageId = $this->_createHeader('Message-ID', 'a@b');
        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Message-ID' => $messageId)),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertEquals('a@b', $message->getId());
    }

    public function testDescriptionIsNullWhenNotSet()
    {
        $message = $this->createMessageWithEmptyHeaderSet();

        $this->assertNull($message->getDescription());
    }

    public function testIdIsSetInHeader()
    {
        $messageId = $this->_createHeader('Message-ID', 'a@b', array(), false);
        $messageId->shouldReceive('setFieldBodyModel')
                  ->once()
                  ->with('x@y');
        $messageId->shouldReceive('setFieldBodyModel')
                  ->zeroOrMoreTimes();

        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Message-ID' => $messageId)),
            $this->_createEncoder(), $this->_createCache()
            );
        $message->setId('x@y');
    }

    public function testIdIsAutoGenerated()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('addIdHeader')
                ->once()
                ->with('Message-ID', '/^.*?@.*?$/D');

        $message = $this->_createMessage($headers, $this->_createEncoder(),
            $this->_createCache()
            );
    }

    public function testSubjectIsReturnedFromHeader()
    {
        /* -- RFC 2822, 3.6.5.
     */

        $subject = $this->_createHeader('Subject', 'example subject');
        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Subject' => $subject)),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertEquals('example subject', $message->getSubject());
    }

    public function testSubjectIsNullWhenNotSet()
    {
        $message = $this->createMessageWithEmptyHeaderSet();

        $this->assertNull($message->getSubject());
    }

    public function testSubjectIsSetInHeader()
    {
        $subject = $this->_createHeader('Subject', '', array(), false);
        $subject->shouldReceive('setFieldBodyModel')
                ->once()
                ->with('foo');

        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Subject' => $subject)),
            $this->_createEncoder(), $this->_createCache()
            );
        $message->setSubject('foo');
    }

    public function testSubjectHeaderIsCreatedIfNotPresent()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('addTextHeader')
                ->once()
                ->with('Subject', 'example subject');
        $headers->shouldReceive('addTextHeader')
                ->zeroOrMoreTimes();

        $message = $this->_createMessage($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $message->setSubject('example subject');
    }

    public function testReturnPathIsReturnedFromHeader()
    {
        /* -- RFC 2822, 3.6.7.
     */

        $path = $this->_createHeader('Return-Path', 'bounces@domain');
        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Return-Path' => $path)),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertEquals('bounces@domain', $message->getReturnPath());
    }

    public function testReturnPathIsNullWhenNotSet()
    {
        $message = $this->createMessageWithEmptyHeaderSet();

        $this->assertNull($message->getReturnPath());
    }

    public function testReturnPathIsSetInHeader()
    {
        $path = $this->_createHeader('Return-Path', '', array(), false);
        $path->shouldReceive('setFieldBodyModel')
             ->once()
             ->with('bounces@domain');

        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Return-Path' => $path)),
            $this->_createEncoder(), $this->_createCache()
            );
        $message->setReturnPath('bounces@domain');
    }

    public function testReturnPathHeaderIsAddedIfNoneSet()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('addPathHeader')
                ->once()
                ->with('Return-Path', 'bounces@domain');

        $message = $this->_createMessage($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $message->setReturnPath('bounces@domain');
    }

    public function testSenderIsReturnedFromHeader()
    {
        /* -- RFC 2822, 3.6.2.
     */

        $sender = $this->_createHeader('Sender', array('sender@domain' => 'Name'));
        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Sender' => $sender)),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertEquals(array('sender@domain' => 'Name'), $message->getSender());
    }

    public function testSenderIsNullWhenNotSet()
    {
        $message = $this->createMessageWithEmptyHeaderSet();

        $this->assertNull($message->getSender());
    }

    public function testSenderIsSetInHeader()
    {
        $sender = $this->_createHeader('Sender', array('sender@domain' => 'Name'),
            array(), false
            );
        $sender->shouldReceive('setFieldBodyModel')
               ->once()
               ->with(array('other@domain' => 'Other'));

        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Sender' => $sender)),
            $this->_createEncoder(), $this->_createCache()
            );
        $message->setSender(array('other@domain' => 'Other'));
    }

    public function testSenderHeaderIsAddedIfNoneSet()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('addMailboxHeader')
                ->once()
                ->with('Sender', (array) 'sender@domain');
        $headers->shouldReceive('addMailboxHeader')
                ->zeroOrMoreTimes();

        $message = $this->_createMessage($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $message->setSender('sender@domain');
    }

    public function testNameCanBeUsedInSenderHeader()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('addMailboxHeader')
                ->once()
                ->with('Sender', array('sender@domain' => 'Name'));
        $headers->shouldReceive('addMailboxHeader')
                ->zeroOrMoreTimes();

        $message = $this->_createMessage($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $message->setSender('sender@domain', 'Name');
    }

    public function testFromIsReturnedFromHeader()
    {
        /* -- RFC 2822, 3.6.2.
     */

        $from = $this->_createHeader('From', array('from@domain' => 'Name'));
        $message = $this->_createMessage(
            $this->_createHeaderSet(array('From' => $from)),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertEquals(array('from@domain' => 'Name'), $message->getFrom());
    }

    public function testFromIsEmptyArrayWhenNotSet()
    {
        $message = $this->createMessageWithEmptyHeaderSet();

        $this->assertEquals(array(), $message->getFrom());
    }

    public function testFromIsSetInHeader()
    {
        $from = $this->_createHeader('From', array('from@domain' => 'Name'),
            array(), false
            );
        $from->shouldReceive('setFieldBodyModel')
             ->once()
             ->with(array('other@domain' => 'Other'));

        $message = $this->_createMessage(
            $this->_createHeaderSet(array('From' => $from)),
            $this->_createEncoder(), $this->_createCache()
            );
        $message->setFrom(array('other@domain' => 'Other'));
    }

    public function testFromIsAddedToHeadersDuringAddFrom()
    {
        $from = $this->_createHeader('From', array('from@domain' => 'Name'),
            array(), false
            );
        $from->shouldReceive('setFieldBodyModel')
             ->once()
             ->with(array('from@domain' => 'Name', 'other@domain' => 'Other'));

        $message = $this->_createMessage(
            $this->_createHeaderSet(array('From' => $from)),
            $this->_createEncoder(), $this->_createCache()
            );
        $message->addFrom('other@domain', 'Other');
    }

    public function testFromHeaderIsAddedIfNoneSet()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('addMailboxHeader')
                ->once()
                ->with('From', (array) 'from@domain');
        $headers->shouldReceive('addMailboxHeader')
                ->zeroOrMoreTimes();

        $message = $this->_createMessage($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $message->setFrom('from@domain');
    }

    public function testPersonalNameCanBeUsedInFromAddress()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('addMailboxHeader')
                ->once()
                ->with('From', array('from@domain' => 'Name'));
        $headers->shouldReceive('addMailboxHeader')
                ->zeroOrMoreTimes();

        $message = $this->_createMessage($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $message->setFrom('from@domain', 'Name');
    }

    public function testReplyToIsReturnedFromHeader()
    {
        /* -- RFC 2822, 3.6.2.
     */

        $reply = $this->_createHeader('Reply-To', array('reply@domain' => 'Name'));
        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Reply-To' => $reply)),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertEquals(array('reply@domain' => 'Name'), $message->getReplyTo());
    }

    public function testReplyToIsNullWhenNotSet()
    {
        $message = $this->createMessageWithEmptyHeaderSet();

        $this->assertNull($message->getReplyTo());
    }

    public function testReplyToIsSetInHeader()
    {
        $reply = $this->_createHeader('Reply-To', array('reply@domain' => 'Name'),
            array(), false
            );
        $reply->shouldReceive('setFieldBodyModel')
              ->once()
              ->with(array('other@domain' => 'Other'));

        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Reply-To' => $reply)),
            $this->_createEncoder(), $this->_createCache()
            );
        $message->setReplyTo(array('other@domain' => 'Other'));
    }

    public function testReplyToIsAddedToHeadersDuringAddReplyTo()
    {
        $replyTo = $this->_createHeader('Reply-To', array('from@domain' => 'Name'),
            array(), false
            );
        $replyTo->shouldReceive('setFieldBodyModel')
                ->once()
                ->with(array('from@domain' => 'Name', 'other@domain' => 'Other'));

        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Reply-To' => $replyTo)),
            $this->_createEncoder(), $this->_createCache()
            );
        $message->addReplyTo('other@domain', 'Other');
    }

    public function testReplyToHeaderIsAddedIfNoneSet()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('addMailboxHeader')
                ->once()
                ->with('Reply-To', (array) 'reply@domain');
        $headers->shouldReceive('addMailboxHeader')
                ->zeroOrMoreTimes();

        $message = $this->_createMessage($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $message->setReplyTo('reply@domain');
    }

    public function testNameCanBeUsedInReplyTo()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('addMailboxHeader')
                ->once()
                ->with('Reply-To', array('reply@domain' => 'Name'));
        $headers->shouldReceive('addMailboxHeader')
                ->zeroOrMoreTimes();

        $message = $this->_createMessage($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $message->setReplyTo('reply@domain', 'Name');
    }

    public function testToIsReturnedFromHeader()
    {
        /* -- RFC 2822, 3.6.3.
     */

        $to = $this->_createHeader('To', array('to@domain' => 'Name'));
        $message = $this->_createMessage(
            $this->_createHeaderSet(array('To' => $to)),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertEquals(array('to@domain' => 'Name'), $message->getTo());
    }

    public function testToIsEmptyArrayWhenNotSet()
    {
        $message = $this->createMessageWithEmptyHeaderSet();

        $this->assertEquals(array(), $message->getTo());
    }

    public function testToIsSetInHeader()
    {
        $to = $this->_createHeader('To', array('to@domain' => 'Name'),
            array(), false
            );
        $to->shouldReceive('setFieldBodyModel')
           ->once()
           ->with(array('other@domain' => 'Other'));

        $message = $this->_createMessage(
            $this->_createHeaderSet(array('To' => $to)),
            $this->_createEncoder(), $this->_createCache()
            );
        $message->setTo(array('other@domain' => 'Other'));
    }

    public function testToIsAddedToHeadersDuringAddTo()
    {
        $to = $this->_createHeader('To', array('from@domain' => 'Name'),
            array(), false
            );
        $to->shouldReceive('setFieldBodyModel')
           ->once()
           ->with(array('from@domain' => 'Name', 'other@domain' => 'Other'));

        $message = $this->_createMessage(
            $this->_createHeaderSet(array('To' => $to)),
            $this->_createEncoder(), $this->_createCache()
            );
        $message->addTo('other@domain', 'Other');
    }

    public function testToHeaderIsAddedIfNoneSet()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('addMailboxHeader')
                ->once()
                ->with('To', (array) 'to@domain');
        $headers->shouldReceive('addMailboxHeader')
                ->zeroOrMoreTimes();

        $message = $this->_createMessage($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $message->setTo('to@domain');
    }

    public function testNameCanBeUsedInToHeader()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('addMailboxHeader')
                ->once()
                ->with('To', array('to@domain' => 'Name'));
        $headers->shouldReceive('addMailboxHeader')
                ->zeroOrMoreTimes();

        $message = $this->_createMessage($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $message->setTo('to@domain', 'Name');
    }

    public function testCcIsReturnedFromHeader()
    {
        /* -- RFC 2822, 3.6.3.
     */

        $cc = $this->_createHeader('Cc', array('cc@domain' => 'Name'));
        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Cc' => $cc)),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertEquals(array('cc@domain' => 'Name'), $message->getCc());
    }

    public function testCcIsEmptyArrayWhenNotSet()
    {
        $message = $this->createMessageWithEmptyHeaderSet();

        $this->assertEquals(array(), $message->getCc());
    }

    public function testCcIsSetInHeader()
    {
        $cc = $this->_createHeader('Cc', array('cc@domain' => 'Name'),
            array(), false
            );
        $cc->shouldReceive('setFieldBodyModel')
           ->once()
           ->with(array('other@domain' => 'Other'));

        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Cc' => $cc)),
            $this->_createEncoder(), $this->_createCache()
            );
        $message->setCc(array('other@domain' => 'Other'));
    }

    public function testCcIsAddedToHeadersDuringAddCc()
    {
        $cc = $this->_createHeader('Cc', array('from@domain' => 'Name'),
            array(), false
            );
        $cc->shouldReceive('setFieldBodyModel')
           ->once()
           ->with(array('from@domain' => 'Name', 'other@domain' => 'Other'));

        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Cc' => $cc)),
            $this->_createEncoder(), $this->_createCache()
            );
        $message->addCc('other@domain', 'Other');
    }

    public function testCcHeaderIsAddedIfNoneSet()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('addMailboxHeader')
                ->once()
                ->with('Cc', (array) 'cc@domain');
        $headers->shouldReceive('addMailboxHeader')
                ->zeroOrMoreTimes();

        $message = $this->_createMessage($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $message->setCc('cc@domain');
    }

    public function testNameCanBeUsedInCcHeader()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('addMailboxHeader')
                ->once()
                ->with('Cc', array('cc@domain' => 'Name'));
        $headers->shouldReceive('addMailboxHeader')
                ->zeroOrMoreTimes();

        $message = $this->_createMessage($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $message->setCc('cc@domain', 'Name');
    }

    public function testBccIsReturnedFromHeader()
    {
        /* -- RFC 2822, 3.6.3.
     */

        $bcc = $this->_createHeader('Bcc', array('bcc@domain' => 'Name'));
        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Bcc' => $bcc)),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertEquals(array('bcc@domain' => 'Name'), $message->getBcc());
    }

    public function testBccIsEmptyArrayWhenNotSet()
    {
        $message = $this->createMessageWithEmptyHeaderSet();

        $this->assertEquals(array(), $message->getBcc());
    }

    public function testBccIsSetInHeader()
    {
        $bcc = $this->_createHeader('Bcc', array('bcc@domain' => 'Name'),
            array(), false
            );
        $bcc->shouldReceive('setFieldBodyModel')
            ->once()
            ->with(array('other@domain' => 'Other'));

        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Bcc' => $bcc)),
            $this->_createEncoder(), $this->_createCache()
            );
        $message->setBcc(array('other@domain' => 'Other'));
    }

    public function testBccIsAddedToHeadersDuringAddBcc()
    {
        $bcc = $this->_createHeader('Bcc', array('from@domain' => 'Name'),
            array(), false
            );
        $bcc->shouldReceive('setFieldBodyModel')
            ->once()
            ->with(array('from@domain' => 'Name', 'other@domain' => 'Other'));

        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Bcc' => $bcc)),
            $this->_createEncoder(), $this->_createCache()
            );
        $message->addBcc('other@domain', 'Other');
    }

    public function testBccHeaderIsAddedIfNoneSet()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('addMailboxHeader')
                ->once()
                ->with('Bcc', (array) 'bcc@domain');
        $headers->shouldReceive('addMailboxHeader')
                ->zeroOrMoreTimes();

        $message = $this->_createMessage($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $message->setBcc('bcc@domain');
    }

    public function testNameCanBeUsedInBcc()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('addMailboxHeader')
                ->once()
                ->with('Bcc', array('bcc@domain' => 'Name'));
        $headers->shouldReceive('addMailboxHeader')
                ->zeroOrMoreTimes();

        $message = $this->_createMessage($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $message->setBcc('bcc@domain', 'Name');
    }

    public function testPriorityIsReadFromHeader()
    {
        $prio = $this->_createHeader('X-Priority', '2 (High)');
        $message = $this->_createMessage(
            $this->_createHeaderSet(array('X-Priority' => $prio)),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertEquals(2, $message->getPriority());
    }

    public function testPriorityDefaultWhenNotSet()
    {
        $message = $this->createMessageWithEmptyHeaderSet();

        $this->assertEquals(3, $message->getPriority());
    }

    public function testPriorityIsSetInHeader()
    {
        $prio = $this->_createHeader('X-Priority', '2 (High)', array(), false);
        $prio->shouldReceive('setFieldBodyModel')
             ->once()
             ->with('5 (Lowest)');

        $message = $this->_createMessage(
            $this->_createHeaderSet(array('X-Priority' => $prio)),
            $this->_createEncoder(), $this->_createCache()
            );
        $message->setPriority(5);
    }

    public function testPriorityHeaderIsAddedIfNoneSet()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('addTextHeader')
                ->once()
                ->with('X-Priority', '4 (Low)');
        $headers->shouldReceive('addTextHeader')
                ->zeroOrMoreTimes();

        $message = $this->_createMessage($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $message->setPriority(4);
    }

    public function testReadReceiptAddressReadFromHeader()
    {
        $rcpt = $this->_createHeader('Disposition-Notification-To',
            array('chris@swiftmailer.org' => 'Chris')
            );
        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Disposition-Notification-To' => $rcpt)),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertEquals(array('chris@swiftmailer.org' => 'Chris'),
            $message->getReadReceiptTo()
            );
    }

    public function testReadReceiptToIsNullWhenNotSet()
    {
        $message = $this->createMessageWithEmptyHeaderSet();

        $this->assertNull($message->getReadReceiptTo());
    }

    public function testContentTypeIsNullWhenNotSet()
    {
        $message = $this->createMessageWithEmptyHeaderSet();

        $this->assertNull($message->getContentType());
    }

    public function testReadReceiptIsSetInHeader()
    {
        $rcpt = $this->_createHeader('Disposition-Notification-To', array(), array(), false);
        $rcpt->shouldReceive('setFieldBodyModel')
             ->once()
             ->with('mark@swiftmailer.org');

        $message = $this->_createMessage(
            $this->_createHeaderSet(array('Disposition-Notification-To' => $rcpt)),
            $this->_createEncoder(), $this->_createCache()
            );
        $message->setReadReceiptTo('mark@swiftmailer.org');
    }

    public function testReadReceiptHeaderIsAddedIfNoneSet()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('addMailboxHeader')
                ->once()
                ->with('Disposition-Notification-To', 'mark@swiftmailer.org');
        $headers->shouldReceive('addMailboxHeader')
                ->zeroOrMoreTimes();

        $message = $this->_createMessage($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $message->setReadReceiptTo('mark@swiftmailer.org');
    }

    public function testChildrenCanBeAttached()
    {
        $child1 = $this->_createChild();
        $child2 = $this->_createChild();

        $message = $this->_createMessage($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );

        $message->attach($child1);
        $message->attach($child2);

        $this->assertEquals(array($child1, $child2), $message->getChildren());
    }

    public function testChildrenCanBeDetached()
    {
        $child1 = $this->_createChild();
        $child2 = $this->_createChild();

        $message = $this->_createMessage($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );

        $message->attach($child1);
        $message->attach($child2);

        $message->detach($child1);

        $this->assertEquals(array($child2), $message->getChildren());
    }

    public function testEmbedAttachesChild()
    {
        $child = $this->_createChild();

        $message = $this->_createMessage($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );

        $message->embed($child);

        $this->assertEquals(array($child), $message->getChildren());
    }

    public function testEmbedReturnsValidCid()
    {
        $child = $this->_createChild(Swift_Mime_MimeEntity::LEVEL_RELATED, '',
            false
            );
        $child->shouldReceive('getId')
              ->zeroOrMoreTimes()
              ->andReturn('foo@bar');

        $message = $this->_createMessage($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );

        $this->assertEquals('cid:foo@bar', $message->embed($child));
    }

    public function testFluidInterface()
    {
        $child = $this->_createChild();
        $message = $this->_createMessage($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertSame($message,
            $message
            ->setContentType('text/plain')
            ->setEncoder($this->_createEncoder())
            ->setId('foo@bar')
            ->setDescription('my description')
            ->setMaxLineLength(998)
            ->setBody('xx')
            ->setBoundary('xyz')
            ->setChildren(array())
            ->setCharset('iso-8859-1')
            ->setFormat('flowed')
            ->setDelSp(false)
            ->setSubject('subj')
            ->setDate(123)
            ->setReturnPath('foo@bar')
            ->setSender('foo@bar')
            ->setFrom(array('x@y' => 'XY'))
            ->setReplyTo(array('ab@cd' => 'ABCD'))
            ->setTo(array('chris@site.tld', 'mark@site.tld'))
            ->setCc('john@somewhere.tld')
            ->setBcc(array('one@site', 'two@site' => 'Two'))
            ->setPriority(4)
            ->setReadReceiptTo('a@b')
            ->attach($child)
            ->detach($child)
            );
    }

    // -- Private helpers

    //abstract
    protected function _createEntity($headers, $encoder, $cache)
    {
        return $this->_createMessage($headers, $encoder, $cache);
    }

    protected function _createMimePart($headers, $encoder, $cache)
    {
        return $this->_createMessage($headers, $encoder, $cache);
    }

    private function _createMessage($headers, $encoder, $cache)
    {
        return new Swift_Mime_SimpleMessage($headers, $encoder, $cache, new Swift_Mime_Grammar());
    }

    private function createMessageWithEmptyHeaderSet()
    {
        return $this->_createMessage(
            $this->_createHeaderSet(array()),
            $this->_createEncoder(), $this->_createCache()
        );
    }
}
