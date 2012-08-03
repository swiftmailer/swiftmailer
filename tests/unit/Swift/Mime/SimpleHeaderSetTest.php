<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Mime/SimpleHeaderSet.php';
require_once 'Swift/Mime/HeaderFactory.php';
require_once 'Swift/Mime/Header.php';

class Swift_Mime_SimpleHeaderSetTest extends Swift_Tests_SwiftUnitTestCase
{
    public function testAddMailboxHeaderDelegatesToFactory()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> one($factory)->createMailboxHeader('From', array('person@domain'=>'Person'))
                -> returns($this->_createHeader('From'))
            );
        $set = $this->_createSet($factory);
        $set->addMailboxHeader('From', array('person@domain'=>'Person'));
    }

    public function testAddDateHeaderDelegatesToFactory()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> one($factory)->createDateHeader('Date', 1234)
                -> returns($this->_createHeader('Date'))
            );
        $set = $this->_createSet($factory);
        $set->addDateHeader('Date', 1234);
    }

    public function testAddTextHeaderDelegatesToFactory()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> one($factory)->createTextHeader('Subject', 'some text')
                -> returns($this->_createHeader('Subject'))
            );
        $set = $this->_createSet($factory);
        $set->addTextHeader('Subject', 'some text');
    }

    public function testAddParameterizedHeaderDelegatesToFactory()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> one($factory)->createParameterizedHeader(
                'Content-Type', 'text/plain', array('charset'=>'utf-8')
                ) -> returns($this->_createHeader('Content-Type'))
            );
        $set = $this->_createSet($factory);
        $set->addParameterizedHeader('Content-Type', 'text/plain',
            array('charset'=>'utf-8')
            );
    }

    public function testAddIdHeaderDelegatesToFactory()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> one($factory)->createIdHeader('Message-ID', 'some@id')
                -> returns($this->_createHeader('Message-ID'))
            );
        $set = $this->_createSet($factory);
        $set->addIdHeader('Message-ID', 'some@id');
    }

    public function testAddPathHeaderDelegatesToFactory()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> one($factory)->createPathHeader('Return-Path', 'some@path')
                -> returns($this->_createHeader('Return-Path'))
            );
        $set = $this->_createSet($factory);
        $set->addPathHeader('Return-Path', 'some@path');
    }

    public function testHasReturnsFalseWhenNoHeaders()
    {
        $set = $this->_createSet($this->_createFactory());
        $this->assertFalse($set->has('Some-Header'));
    }

    public function testAddedMailboxHeaderIsSeenByHas()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createMailboxHeader('From', array('person@domain'=>'Person'))
                -> returns($this->_createHeader('From'))
            );
        $set = $this->_createSet($factory);
        $set->addMailboxHeader('From', array('person@domain'=>'Person'));
        $this->assertTrue($set->has('From'));
    }

    public function testAddedDateHeaderIsSeenByHas()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createDateHeader('Date', 1234)
                -> returns($this->_createHeader('Date'))
            );
        $set = $this->_createSet($factory);
        $set->addDateHeader('Date', 1234);
        $this->assertTrue($set->has('Date'));
    }

    public function testAddedTextHeaderIsSeenByHas()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createTextHeader('Subject', 'some text')
                -> returns($this->_createHeader('Subject'))
            );
        $set = $this->_createSet($factory);
        $set->addTextHeader('Subject', 'some text');
        $this->assertTrue($set->has('Subject'));
    }

    public function testAddedParameterizedHeaderIsSeenByHas()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createParameterizedHeader(
                'Content-Type', 'text/plain', array('charset'=>'utf-8')
                ) -> returns($this->_createHeader('Content-Type'))
            );
        $set = $this->_createSet($factory);
        $set->addParameterizedHeader('Content-Type', 'text/plain',
            array('charset'=>'utf-8')
            );
        $this->assertTrue($set->has('Content-Type'));
    }

    public function testAddedIdHeaderIsSeenByHas()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createIdHeader('Message-ID', 'some@id')
                -> returns($this->_createHeader('Message-ID'))
            );
        $set = $this->_createSet($factory);
        $set->addIdHeader('Message-ID', 'some@id');
        $this->assertTrue($set->has('Message-ID'));
    }

    public function testAddedPathHeaderIsSeenByHas()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createPathHeader('Return-Path', 'some@path')
                -> returns($this->_createHeader('Return-Path'))
            );
        $set = $this->_createSet($factory);
        $set->addPathHeader('Return-Path', 'some@path');
        $this->assertTrue($set->has('Return-Path'));
    }

    public function testNewlySetHeaderIsSeenByHas()
    {
        $factory = $this->_createFactory();
        $header = $this->_createHeader('X-Foo', 'bar');
        $set = $this->_createSet($factory);
        $set->set($header);
        $this->assertTrue($set->has('X-Foo'));
    }

    public function testHasCanAcceptOffset()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createIdHeader('Message-ID', 'some@id')
                -> returns($this->_createHeader('Message-ID'))
            );
        $set = $this->_createSet($factory);
        $set->addIdHeader('Message-ID', 'some@id');
        $this->assertTrue($set->has('Message-ID', 0));
    }

    public function testHasWithIllegalOffsetReturnsFalse()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createIdHeader('Message-ID', 'some@id')
                -> returns($this->_createHeader('Message-ID'))
            );
        $set = $this->_createSet($factory);
        $set->addIdHeader('Message-ID', 'some@id');
        $this->assertFalse($set->has('Message-ID', 1));
    }

    public function testHasCanDistinguishMultipleHeaders()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createIdHeader('Message-ID', 'some@id')
                -> returns($this->_createHeader('Message-ID'))
            -> ignoring($factory)->createIdHeader('Message-ID', 'other@id')
                -> returns($this->_createHeader('Message-ID'))
            );
        $set = $this->_createSet($factory);
        $set->addIdHeader('Message-ID', 'some@id');
        $set->addIdHeader('Message-ID', 'other@id');
        $this->assertTrue($set->has('Message-ID', 1));
    }

    public function testGetWithUnspecifiedOffset()
    {
        $header = $this->_createHeader('Message-ID');
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createIdHeader('Message-ID', 'some@id')
                -> returns($header)
            );
        $set = $this->_createSet($factory);
        $set->addIdHeader('Message-ID', 'some@id');
        $this->assertSame($header, $set->get('Message-ID'));
    }

    public function testGetWithSpeiciedOffset()
    {
        $header0 = $this->_createHeader('Message-ID');
        $header1 = $this->_createHeader('Message-ID');
        $header2 = $this->_createHeader('Message-ID');
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createIdHeader('Message-ID', 'some@id')
                -> returns($header0)
            -> ignoring($factory)->createIdHeader('Message-ID', 'other@id')
                -> returns($header1)
            -> ignoring($factory)->createIdHeader('Message-ID', 'more@id')
                -> returns($header2)
            );
        $set = $this->_createSet($factory);
        $set->addIdHeader('Message-ID', 'some@id');
        $set->addIdHeader('Message-ID', 'other@id');
        $set->addIdHeader('Message-ID', 'more@id');
        $this->assertSame($header1, $set->get('Message-ID', 1));
    }

    public function testGetReturnsNullIfHeaderNotSet()
    {
        $set = $this->_createSet($this->_createFactory());
        $this->assertNull($set->get('Message-ID', 99));
    }

    public function testGetAllReturnsAllHeadersMatchingName()
    {
        $header0 = $this->_createHeader('Message-ID');
        $header1 = $this->_createHeader('Message-ID');
        $header2 = $this->_createHeader('Message-ID');
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createIdHeader('Message-ID', 'some@id')
                -> returns($header0)
            -> ignoring($factory)->createIdHeader('Message-ID', 'other@id')
                -> returns($header1)
            -> ignoring($factory)->createIdHeader('Message-ID', 'more@id')
                -> returns($header2)
            );
        $set = $this->_createSet($factory);
        $set->addIdHeader('Message-ID', 'some@id');
        $set->addIdHeader('Message-ID', 'other@id');
        $set->addIdHeader('Message-ID', 'more@id');

        $this->assertEqual(array($header0, $header1, $header2),
            $set->getAll('Message-ID')
            );
    }

    public function testGetAllReturnsAllHeadersIfNoArguments()
    {
        $header0 = $this->_createHeader('Message-ID');
        $header1 = $this->_createHeader('Subject');
        $header2 = $this->_createHeader('To');
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createIdHeader('Message-ID', 'some@id')
                -> returns($header0)
            -> ignoring($factory)->createIdHeader('Subject', 'thing')
                -> returns($header1)
            -> ignoring($factory)->createIdHeader('To', 'person@example.org')
                -> returns($header2)
            );
        $set = $this->_createSet($factory);
        $set->addIdHeader('Message-ID', 'some@id');
        $set->addIdHeader('Subject', 'thing');
        $set->addIdHeader('To', 'person@example.org');

        $this->assertEqual(array($header0, $header1, $header2),
            $set->getAll()
            );
    }

    public function testGetAllReturnsEmptyArrayIfNoneSet()
    {
        $set = $this->_createSet($this->_createFactory());
        $this->assertEqual(array(), $set->getAll('Received'));
    }

    public function testRemoveWithUnspecifiedOffset()
    {
        $header = $this->_createHeader('Message-ID');
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createIdHeader('Message-ID', 'some@id')
                -> returns($header)
            );
        $set = $this->_createSet($factory);
        $set->addIdHeader('Message-ID', 'some@id');
        $set->remove('Message-ID');
        $this->assertFalse($set->has('Message-ID'));
    }

    public function testRemoveWithSpecifiedIndexRemovesHeader()
    {
        $header0 = $this->_createHeader('Message-ID');
        $header1 = $this->_createHeader('Message-ID');
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createIdHeader('Message-ID', 'some@id')
                -> returns($header0)
            -> ignoring($factory)->createIdHeader('Message-ID', 'other@id')
                -> returns($header1)
            );
        $set = $this->_createSet($factory);
        $set->addIdHeader('Message-ID', 'some@id');
        $set->addIdHeader('Message-ID', 'other@id');
        $set->remove('Message-ID', 1);
        $this->assertFalse($set->has('Message-ID', 1));
    }

    public function testRemoveWithSpecifiedIndexLeavesOtherHeaders()
    {
        $header0 = $this->_createHeader('Message-ID');
        $header1 = $this->_createHeader('Message-ID');
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createIdHeader('Message-ID', 'some@id')
                -> returns($header0)
            -> ignoring($factory)->createIdHeader('Message-ID', 'other@id')
                -> returns($header1)
            );
        $set = $this->_createSet($factory);
        $set->addIdHeader('Message-ID', 'some@id');
        $set->addIdHeader('Message-ID', 'other@id');
        $set->remove('Message-ID', 1);
        $this->assertTrue($set->has('Message-ID', 0));
    }

    public function testRemoveWithInvalidOffsetDoesNothing()
    {
        $header = $this->_createHeader('Message-ID');
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createIdHeader('Message-ID', 'some@id')
                -> returns($header)
            );
        $set = $this->_createSet($factory);
        $set->addIdHeader('Message-ID', 'some@id');
        $set->remove('Message-ID', 50);
        $this->assertTrue($set->has('Message-ID'));
    }

    public function testRemoveAllRemovesAllHeadersWithName()
    {
        $header0 = $this->_createHeader('Message-ID');
        $header1 = $this->_createHeader('Message-ID');
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createIdHeader('Message-ID', 'some@id')
                -> returns($header0)
            -> ignoring($factory)->createIdHeader('Message-ID', 'other@id')
                -> returns($header1)
            );
        $set = $this->_createSet($factory);
        $set->addIdHeader('Message-ID', 'some@id');
        $set->addIdHeader('Message-ID', 'other@id');
        $set->removeAll('Message-ID');
        $this->assertFalse($set->has('Message-ID', 0));
        $this->assertFalse($set->has('Message-ID', 1));
    }

    public function testHasIsNotCaseSensitive()
    {
        $header = $this->_createHeader('Message-ID');
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createIdHeader('Message-ID', 'some@id')
                -> returns($header)
            );
        $set = $this->_createSet($factory);
        $set->addIdHeader('Message-ID', 'some@id');
        $this->assertTrue($set->has('message-id'));
    }

    public function testGetIsNotCaseSensitive()
    {
        $header = $this->_createHeader('Message-ID');
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createIdHeader('Message-ID', 'some@id')
                -> returns($header)
            );
        $set = $this->_createSet($factory);
        $set->addIdHeader('Message-ID', 'some@id');
        $this->assertSame($header, $set->get('message-id'));
    }

    public function testGetAllIsNotCaseSensitive()
    {
        $header = $this->_createHeader('Message-ID');
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createIdHeader('Message-ID', 'some@id')
                -> returns($header)
            );
        $set = $this->_createSet($factory);
        $set->addIdHeader('Message-ID', 'some@id');
        $this->assertEqual(array($header), $set->getAll('message-id'));
    }

    public function testRemoveIsNotCaseSensitive()
    {
        $header = $this->_createHeader('Message-ID');
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createIdHeader('Message-ID', 'some@id')
                -> returns($header)
            );
        $set = $this->_createSet($factory);
        $set->addIdHeader('Message-ID', 'some@id');
        $set->remove('message-id');
        $this->assertFalse($set->has('Message-ID'));
    }

    public function testRemoveAllIsNotCaseSensitive()
    {
        $header = $this->_createHeader('Message-ID');
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createIdHeader('Message-ID', 'some@id')
                -> returns($header)
            );
        $set = $this->_createSet($factory);
        $set->addIdHeader('Message-ID', 'some@id');
        $set->removeAll('message-id');
        $this->assertFalse($set->has('Message-ID'));
    }

    public function testNewInstance()
    {
        $set = $this->_createSet($this->_createFactory());
        $instance = $set->newInstance();
        $this->assertIsA($instance, 'Swift_Mime_HeaderSet');
    }

    public function testToStringJoinsHeadersTogether()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> one($factory)->createTextHeader('Foo', 'bar')
                -> returns($this->_createHeader('Foo', 'bar'))
            -> one($factory)->createTextHeader('Zip', 'buttons')
                -> returns($this->_createHeader('Zip', 'buttons'))
            );
        $set = $this->_createSet($factory);
        $set->addTextHeader('Foo', 'bar');
        $set->addTextHeader('Zip', 'buttons');
        $this->assertEqual(
            "Foo: bar\r\n" .
            "Zip: buttons\r\n",
            $set->toString()
            );
    }

    public function testHeadersWithoutBodiesAreNotDisplayed()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> one($factory)->createTextHeader('Foo', 'bar')
                -> returns($this->_createHeader('Foo', 'bar'))
            -> one($factory)->createTextHeader('Zip', '')
                -> returns($this->_createHeader('Zip', ''))
            );
        $set = $this->_createSet($factory);
        $set->addTextHeader('Foo', 'bar');
        $set->addTextHeader('Zip', '');
        $this->assertEqual(
            "Foo: bar\r\n",
            $set->toString()
            );
    }

    public function testHeadersWithoutBodiesCanBeForcedToDisplay()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> one($factory)->createTextHeader('Foo', '')
                -> returns($this->_createHeader('Foo', ''))
            -> one($factory)->createTextHeader('Zip', '')
                -> returns($this->_createHeader('Zip', ''))
            );
        $set = $this->_createSet($factory);
        $set->addTextHeader('Foo', '');
        $set->addTextHeader('Zip', '');
        $set->setAlwaysDisplayed(array('Foo', 'Zip'));
        $this->assertEqual(
            "Foo: \r\n" .
            "Zip: \r\n",
            $set->toString()
            );
    }

    public function testHeaderSequencesCanBeSpecified()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> one($factory)->createTextHeader('First', 'one')
                -> returns($this->_createHeader('First', 'one'))
            -> one($factory)->createTextHeader('Second', 'two')
                -> returns($this->_createHeader('Second', 'two'))
            -> one($factory)->createTextHeader('Third', 'three')
                -> returns($this->_createHeader('Third', 'three'))
            );
        $set = $this->_createSet($factory);
        $set->addTextHeader('Third', 'three');
        $set->addTextHeader('First', 'one');
        $set->addTextHeader('Second', 'two');

        $set->defineOrdering(array('First', 'Second', 'Third'));

        $this->assertEqual(
            "First: one\r\n" .
            "Second: two\r\n" .
            "Third: three\r\n",
            $set->toString()
            );
    }

    public function testUnsortedHeadersAppearAtEnd()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> one($factory)->createTextHeader('First', 'one')
                -> returns($this->_createHeader('First', 'one'))
            -> one($factory)->createTextHeader('Second', 'two')
                -> returns($this->_createHeader('Second', 'two'))
            -> one($factory)->createTextHeader('Third', 'three')
                -> returns($this->_createHeader('Third', 'three'))
            -> one($factory)->createTextHeader('Fourth', 'four')
                -> returns($this->_createHeader('Fourth', 'four'))
            -> one($factory)->createTextHeader('Fifth', 'five')
                -> returns($this->_createHeader('Fifth', 'five'))
            );
        $set = $this->_createSet($factory);
        $set->addTextHeader('Fourth', 'four');
        $set->addTextHeader('Fifth', 'five');
        $set->addTextHeader('Third', 'three');
        $set->addTextHeader('First', 'one');
        $set->addTextHeader('Second', 'two');

        $set->defineOrdering(array('First', 'Second', 'Third'));

        $this->assertEqual(
            "First: one\r\n" .
            "Second: two\r\n" .
            "Third: three\r\n" .
            "Fourth: four\r\n" .
            "Fifth: five\r\n",
            $set->toString()
            );
    }

    public function testSettingCharsetNotifiesAlreadyExistingHeaders()
    {
        $subject = $this->_createHeader('Subject', 'some text');
        $xHeader = $this->_createHeader('X-Header', 'some text');
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createTextHeader('Subject', 'some text')
                -> returns($subject)
            -> ignoring($factory)->createTextHeader('X-Header', 'some text')
                -> returns($xHeader)
            -> ignoring($factory)
            -> one($subject)->setCharset('utf-8')
            -> one($xHeader)->setCharset('utf-8')
            );
        $set = $this->_createSet($factory);
        $set->addTextHeader('Subject', 'some text');
        $set->addTextHeader('X-Header', 'some text');

        $set->setCharset('utf-8');
    }

    public function testCharsetChangeNotifiesAlreadyExistingHeaders()
    {
        $subject = $this->_createHeader('Subject', 'some text');
        $xHeader = $this->_createHeader('X-Header', 'some text');
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> ignoring($factory)->createTextHeader('Subject', 'some text')
                -> returns($subject)
            -> ignoring($factory)->createTextHeader('X-Header', 'some text')
                -> returns($xHeader)
            -> ignoring($factory)
            -> one($subject)->setCharset('utf-8')
            -> one($xHeader)->setCharset('utf-8')
            );
        $set = $this->_createSet($factory);
        $set->addTextHeader('Subject', 'some text');
        $set->addTextHeader('X-Header', 'some text');

        $set->charsetChanged('utf-8');
    }

    public function testCharsetChangeNotifiesFactory()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> one($factory)->charsetChanged('utf-8')
            -> ignoring($factory)
            );
        $set = $this->_createSet($factory);

        $set->setCharset('utf-8');
    }

    // -- Creation methods

    private function _createSet($factory)
    {
        return new Swift_Mime_SimpleHeaderSet($factory);
    }

    private function _createFactory()
    {
        return $this->_mock('Swift_Mime_HeaderFactory');
    }

    private function _createHeader($name, $body = '')
    {
        $header = $this->_mock('Swift_Mime_Header');
        $this->_checking(Expectations::create()
            -> ignoring($header)->getFieldName() -> returns($name)
            -> ignoring($header)->toString() -> returns(sprintf("%s: %s\r\n", $name, $body))
            -> ignoring($header)->getFieldBody() -> returns($body)
            );

        return $header;
    }
}
