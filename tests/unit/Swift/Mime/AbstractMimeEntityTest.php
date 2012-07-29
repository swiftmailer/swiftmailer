<?php

require_once 'Swift/Mime/MimeEntity.php';
require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Mime/ContentEncoder.php';
require_once 'Swift/Mime/Header.php';
require_once 'Swift/Mime/ParameterizedHeader.php';
require_once 'Swift/KeyCache.php';
require_once 'Swift/Mime/HeaderSet.php';

abstract class Swift_Mime_AbstractMimeEntityTest
    extends Swift_Tests_SwiftUnitTestCase
{
    public function testGetHeadersReturnsHeaderSet()
    {
        $headers = $this->_createHeaderSet();
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $this->assertSame($headers, $entity->getHeaders());
    }

    public function testContentTypeIsReturnedFromHeader()
    {
        $ctype = $this->_createHeader('Content-Type', 'image/jpeg-test');
        $headers = $this->_createHeaderSet(array('Content-Type' => $ctype));
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $this->assertEqual('image/jpeg-test', $entity->getContentType());
    }

    public function testContentTypeIsSetInHeader()
    {
        $ctype = $this->_createHeader('Content-Type', 'text/plain', array(), false);
        $headers = $this->_createHeaderSet(array('Content-Type' => $ctype));
        $this->_checking(Expectations::create()
            -> one($ctype)->setFieldBodyModel('image/jpeg')
            -> ignoring($ctype)
            );
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $entity->setContentType('image/jpeg');
    }

    public function testContentTypeHeaderIsAddedIfNoneSet()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $this->_checking(Expectations::create()
            -> one($headers)->addParameterizedHeader('Content-Type', 'image/jpeg')
            -> ignoring($headers)
            );
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $entity->setContentType('image/jpeg');
    }

    public function testContentTypeCanBeSetViaSetBody()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $this->_checking(Expectations::create()
            -> one($headers)->addParameterizedHeader('Content-Type', 'text/html')
            -> ignoring($headers)
            );
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $entity->setBody('<b>foo</b>', 'text/html');
    }

    public function testGetEncoderFromConstructor()
    {
        $encoder = $this->_createEncoder('base64');
        $entity = $this->_createEntity($this->_createHeaderSet(), $encoder,
            $this->_createCache()
            );
        $this->assertSame($encoder, $entity->getEncoder());
    }

    public function testSetAndGetEncoder()
    {
        $encoder = $this->_createEncoder('base64');
        $headers = $this->_createHeaderSet();
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $entity->setEncoder($encoder);
        $this->assertSame($encoder, $entity->getEncoder());
    }

    public function testSettingEncoderUpdatesTransferEncoding()
    {
        $encoder = $this->_createEncoder('base64');
        $encoding = $this->_createHeader(
            'Content-Transfer-Encoding', '8bit', array(), false
            );
        $headers = $this->_createHeaderSet(array(
            'Content-Transfer-Encoding' => $encoding
            ));
        $this->_checking(Expectations::create()
            -> one($encoding)->setFieldBodyModel('base64')
            -> ignoring($encoding)
            );
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $entity->setEncoder($encoder);
    }

    public function testSettingEncoderAddsEncodingHeaderIfNonePresent()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $this->_checking(Expectations::create()
            -> one($headers)->addTextHeader('Content-Transfer-Encoding', 'something')
            -> ignoring($headers)
            );
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $entity->setEncoder($this->_createEncoder('something'));
    }

    public function testIdIsReturnedFromHeader()
    {
        /* -- RFC 2045, 7.
        In constructing a high-level user agent, it may be desirable to allow
        one body to make reference to another.  Accordingly, bodies may be
        labelled using the "Content-ID" header field, which is syntactically
        identical to the "Message-ID" header field
        */

        $cid = $this->_createHeader('Content-ID', 'zip@button');
        $headers = $this->_createHeaderSet(array('Content-ID' => $cid));
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $this->assertEqual('zip@button', $entity->getId());
    }

    public function testIdIsSetInHeader()
    {
        $cid = $this->_createHeader('Content-ID', 'zip@button', array(), false);
        $headers = $this->_createHeaderSet(array('Content-ID' => $cid));
        $this->_checking(Expectations::create()
            -> one($cid)->setFieldBodyModel('foo@bar')
            -> ignoring($cid)
            );
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $entity->setId('foo@bar');
    }

    public function testIdIsAutoGenerated()
    {
        $entity = $this->_createEntity($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertPattern('/^.*?@.*?$/D', $entity->getId());
    }

    public function testGenerateIdCreatesNewId()
    {
        $headers = $this->_createHeaderSet();
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $id1 = $entity->generateId();
        $id2 = $entity->generateId();
        $this->assertNotEqual($id1, $id2);
    }

    public function testGenerateIdSetsNewId()
    {
        $headers = $this->_createHeaderSet();
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $id = $entity->generateId();
        $this->assertEqual($id, $entity->getId());
    }

    public function testDescriptionIsReadFromHeader()
    {
        /* -- RFC 2045, 8.
        The ability to associate some descriptive information with a given
        body is often desirable.  For example, it may be useful to mark an
        "image" body as "a picture of the Space Shuttle Endeavor."  Such text
        may be placed in the Content-Description header field.  This header
        field is always optional.
        */

        $desc = $this->_createHeader('Content-Description', 'something');
        $headers = $this->_createHeaderSet(array('Content-Description' => $desc));
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $this->assertEqual('something', $entity->getDescription());
    }

    public function testDescriptionIsSetInHeader()
    {
        $desc = $this->_createHeader('Content-Description', '', array(), false);
        $headers = $this->_createHeaderSet(array('Content-Description' => $desc));
        $this->_checking(Expectations::create()
            -> one($desc)->setFieldBodyModel('whatever')
            -> ignoring($desc)
            );
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $entity->setDescription('whatever');
    }

    public function testDescriptionHeaderIsAddedIfNotPresent()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $this->_checking(Expectations::create()
            -> one($headers)->addTextHeader('Content-Description', 'whatever')
            -> ignoring($headers)
            );
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $entity->setDescription('whatever');
    }

    public function testSetAndGetMaxLineLength()
    {
        $entity = $this->_createEntity($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );
        $entity->setMaxLineLength(60);
        $this->assertEqual(60, $entity->getMaxLineLength());
    }

    public function testEncoderIsUsedForStringGeneration()
    {
        $encoder = $this->_createEncoder('base64', false);
        $this->_checking(Expectations::create()
            -> one($encoder)->encodeString('blah', optional())
            -> ignoring($encoder)
            );
        $entity = $this->_createEntity($this->_createHeaderSet(),
            $encoder, $this->_createCache()
            );
        $entity->setBody("blah");
        $entity->toString();
    }

    public function testMaxLineLengthIsProvidedWhenEncoding()
    {
        $encoder = $this->_createEncoder('base64', false);
        $this->_checking(Expectations::create()
            -> one($encoder)->encodeString('blah', 0, 65)
            -> ignoring($encoder)
            );
        $entity = $this->_createEntity($this->_createHeaderSet(),
            $encoder, $this->_createCache()
            );
        $entity->setBody("blah");
        $entity->setMaxLineLength(65);
        $entity->toString();
    }

    public function testHeadersAppearInString()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $this->_checking(Expectations::create()
            -> ignoring($headers)->toString() -> returns(
                "Content-Type: text/plain; charset=utf-8\r\n" .
                "X-MyHeader: foobar\r\n"
                )
            -> ignoring($headers)
            );
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $this->assertEqual(
            "Content-Type: text/plain; charset=utf-8\r\n" .
            "X-MyHeader: foobar\r\n",
            $entity->toString()
            );
    }

    public function testSetAndGetBody()
    {
        $entity = $this->_createEntity($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );
        $entity->setBody("blah\r\nblah!");
        $this->assertEqual("blah\r\nblah!", $entity->getBody());
    }

    public function testBodyIsAppended()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $this->_checking(Expectations::create()
            -> ignoring($headers)->toString() -> returns(
                "Content-Type: text/plain; charset=utf-8\r\n"
                )
            -> ignoring($headers)
            );
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $entity->setBody("blah\r\nblah!");
        $this->assertEqual(
            "Content-Type: text/plain; charset=utf-8\r\n" .
            "\r\n" .
            "blah\r\nblah!",
            $entity->toString()
            );
    }

    public function testGetBodyReturnsStringFromByteStream()
    {
        $os = $this->_createOutputStream("byte stream string");
        $entity = $this->_createEntity($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );
        $entity->setBody($os);
        $this->assertEqual("byte stream string", $entity->getBody());
    }

    public function testByteStreamBodyIsAppended()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $os = $this->_createOutputStream("streamed");
        $this->_checking(Expectations::create()
            -> ignoring($headers)->toString() -> returns(
                "Content-Type: text/plain; charset=utf-8\r\n"
                )
            -> ignoring($headers)
            );
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $entity->setBody($os);
        $this->assertEqual(
            "Content-Type: text/plain; charset=utf-8\r\n" .
            "\r\n" .
            "streamed",
            $entity->toString()
            );
    }

    public function testBoundaryCanBeRetrieved()
    {
        /* -- RFC 2046, 5.1.1.
     boundary := 0*69<bchars> bcharsnospace

     bchars := bcharsnospace / " "

     bcharsnospace := DIGIT / ALPHA / "'" / "(" / ")" /
                                            "+" / "_" / "," / "-" / "." /
                                            "/" / ":" / "=" / "?"
        */

        $entity = $this->_createEntity($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertPattern(
            '/^[a-zA-Z0-9\'\(\)\+_\-,\.\/:=\?\ ]{0,69}[a-zA-Z0-9\'\(\)\+_\-,\.\/:=\?]$/D',
            $entity->getBoundary()
            );
    }

    public function testBoundaryNeverChanges()
    {
        $entity = $this->_createEntity($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );
        $firstBoundary = $entity->getBoundary();
        for ($i = 0; $i < 10; $i++) {
            $this->assertEqual($firstBoundary, $entity->getBoundary());
        }
    }

    public function testBoundaryCanBeSet()
    {
        $entity = $this->_createEntity($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );
        $entity->setBoundary('foobar');
        $this->assertEqual('foobar', $entity->getBoundary());
    }

    public function testAddingChildrenGeneratesBoundaryInHeaders()
    {
        $child = $this->_createChild();
        $cType = $this->_createHeader('Content-Type', 'text/plain', array(), false);
        $this->_checking(Expectations::create()
            -> one($cType)->setParameter('boundary', any())
            -> ignoring($cType)
            );

        $entity = $this->_createEntity($this->_createHeaderSet(array(
            'Content-Type' => $cType
            )),
            $this->_createEncoder(), $this->_createCache()
            );
        $entity->setChildren(array($child));
    }

    public function testChildrenOfLevelAttachmentAndLessCauseMultipartMixed()
    {
        for ($level = Swift_Mime_MimeEntity::LEVEL_MIXED;
            $level > Swift_Mime_MimeEntity::LEVEL_TOP; $level /= 2)
        {
            $child = $this->_createChild($level);
            $cType = $this->_createHeader(
                'Content-Type', 'text/plain', array(), false
                );
            $this->_checking(Expectations::create()
                -> one($cType)->setFieldBodyModel('multipart/mixed')
                -> ignoring($cType)
                );
            $entity = $this->_createEntity($this->_createHeaderSet(array(
                'Content-Type' => $cType)),
                $this->_createEncoder(), $this->_createCache()
                );
            $entity->setChildren(array($child));
        }
    }

    public function testChildrenOfLevelAlternativeAndLessCauseMultipartAlternative()
    {
        for ($level = Swift_Mime_MimeEntity::LEVEL_ALTERNATIVE;
            $level > Swift_Mime_MimeEntity::LEVEL_MIXED; $level /= 2)
        {
            $child = $this->_createChild($level);
            $cType = $this->_createHeader(
                'Content-Type', 'text/plain', array(), false
                );
            $this->_checking(Expectations::create()
                -> one($cType)->setFieldBodyModel('multipart/alternative')
                -> ignoring($cType)
                );
            $entity = $this->_createEntity($this->_createHeaderSet(array(
                'Content-Type' => $cType)),
                $this->_createEncoder(), $this->_createCache()
                );
            $entity->setChildren(array($child));
        }
    }

    public function testChildrenOfLevelRelatedAndLessCauseMultipartRelated()
    {
        for ($level = Swift_Mime_MimeEntity::LEVEL_RELATED;
            $level > Swift_Mime_MimeEntity::LEVEL_ALTERNATIVE; $level /= 2)
        {
            $child = $this->_createChild($level);
            $cType = $this->_createHeader(
                'Content-Type', 'text/plain', array(), false
                );
            $this->_checking(Expectations::create()
                -> one($cType)->setFieldBodyModel('multipart/related')
                -> ignoring($cType)
                );
            $entity = $this->_createEntity($this->_createHeaderSet(array(
                'Content-Type' => $cType)),
                $this->_createEncoder(), $this->_createCache()
                );
            $entity->setChildren(array($child));
        }
    }

    public function testHighestLevelChildDeterminesContentType()
    {
        $combinations  = array(
            array('levels' => array(Swift_Mime_MimeEntity::LEVEL_MIXED,
                Swift_Mime_MimeEntity::LEVEL_ALTERNATIVE,
                Swift_Mime_MimeEntity::LEVEL_RELATED
                ),
                'type' => 'multipart/mixed'
                ),
            array('levels' => array(Swift_Mime_MimeEntity::LEVEL_MIXED,
                Swift_Mime_MimeEntity::LEVEL_RELATED
                ),
                'type' => 'multipart/mixed'
                ),
            array('levels' => array(Swift_Mime_MimeEntity::LEVEL_MIXED,
                Swift_Mime_MimeEntity::LEVEL_ALTERNATIVE
                ),
                'type' => 'multipart/mixed'
                ),
            array('levels' => array(Swift_Mime_MimeEntity::LEVEL_ALTERNATIVE,
                Swift_Mime_MimeEntity::LEVEL_RELATED
                ),
                'type' => 'multipart/alternative'
                )
            );

        foreach ($combinations as $combination) {
            $children = array();
            foreach ($combination['levels'] as $level) {
                $children[] = $this->_createChild($level);
            }

            $cType = $this->_createHeader(
                'Content-Type', 'text/plain', array(), false
                );
            $this->_checking(Expectations::create()
                -> one($cType)->setFieldBodyModel($combination['type'])
                -> ignoring($cType)
                );
            $entity = $this->_createEntity($this->_createHeaderSet(array(
                'Content-Type' => $cType)),
                $this->_createEncoder(), $this->_createCache()
                );
            $entity->setChildren($children);
        }
    }

    public function testChildrenAppearNestedInString()
    {
        /* -- RFC 2046, 5.1.1.
     (excerpt too verbose to paste here)
     */

        $headers = $this->_createHeaderSet(array(), false);

        $child1 = $this->_createChild(Swift_Mime_MimeEntity::LEVEL_ALTERNATIVE,
            "Content-Type: text/plain\r\n" .
            "\r\n" .
            "foobar"
            );

        $child2 = $this->_createChild(Swift_Mime_MimeEntity::LEVEL_ALTERNATIVE,
            "Content-Type: text/html\r\n" .
            "\r\n" .
            "<b>foobar</b>"
            );

        $this->_checking(Expectations::create()
            -> ignoring($headers)->toString() -> returns(
                "Content-Type: multipart/alternative; boundary=\"xxx\"\r\n"
                )
            -> ignoring($headers)
            );

        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $entity->setBoundary('xxx');
        $entity->setChildren(array($child1, $child2));

        $this->assertEqual(
            "Content-Type: multipart/alternative; boundary=\"xxx\"\r\n" .
            "\r\n" .
            "\r\n--xxx\r\n" .
            "Content-Type: text/plain\r\n" .
            "\r\n" .
            "foobar\r\n" .
            "\r\n--xxx\r\n" .
            "Content-Type: text/html\r\n" .
            "\r\n" .
            "<b>foobar</b>\r\n" .
            "\r\n--xxx--\r\n",
            $entity->toString()
            );
    }

    public function testMixingLevelsIsHierarchical()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $newHeaders = $this->_createHeaderSet(array(), false);

        $part = $this->_createChild(Swift_Mime_MimeEntity::LEVEL_ALTERNATIVE,
            "Content-Type: text/plain\r\n" .
            "\r\n" .
            "foobar"
            );

        $attachment = $this->_createChild(Swift_Mime_MimeEntity::LEVEL_MIXED,
            "Content-Type: application/octet-stream\r\n" .
            "\r\n" .
            "data"
            );

        $this->_checking(Expectations::create()
            -> ignoring($headers)->toString() -> returns(
                "Content-Type: multipart/mixed; boundary=\"xxx\"\r\n"
                )
            -> ignoring($headers)->newInstance() -> returns($newHeaders)
            -> ignoring($headers)
            -> ignoring($newHeaders)->toString() -> returns(
                "Content-Type: multipart/alternative; boundary=\"yyy\"\r\n"
                )
            -> ignoring($newHeaders)
            );

        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $entity->setBoundary('xxx');
        $entity->setChildren(array($part, $attachment));

        $this->assertPattern(
            "~^" .
            "Content-Type: multipart/mixed; boundary=\"xxx\"\r\n" .
            "\r\n\r\n--xxx\r\n" .
            "Content-Type: multipart/alternative; boundary=\"yyy\"\r\n" .
            "\r\n\r\n--(.*?)\r\n" .
            "Content-Type: text/plain\r\n" .
            "\r\n" .
            "foobar" .
            "\r\n\r\n--\\1--\r\n" .
            "\r\n\r\n--xxx\r\n" .
            "Content-Type: application/octet-stream\r\n" .
            "\r\n" .
            "data" .
            "\r\n\r\n--xxx--\r\n" .
            "\$~",
            $entity->toString()
            );
    }

    public function testSettingEncoderNotifiesChildren()
    {
        $child = $this->_createChild(0, '', false);
        $encoder = $this->_createEncoder('base64');

        $this->_checking(Expectations::create()
            -> one($child)->encoderChanged($encoder)
            -> ignoring($child)
            );

        $entity = $this->_createEntity($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );
        $entity->setChildren(array($child));
        $entity->setEncoder($encoder);
    }

    public function testReceiptOfEncoderChangeNotifiesChildren()
    {
        $child = $this->_createChild(0, '', false);
        $encoder = $this->_createEncoder('base64');

        $this->_checking(Expectations::create()
            -> one($child)->encoderChanged($encoder)
            -> ignoring($child)
            );

        $entity = $this->_createEntity($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );
        $entity->setChildren(array($child));
        $entity->encoderChanged($encoder);
    }

    public function testReceiptOfCharsetChangeNotifiesChildren()
    {
        $child = $this->_createChild(0, '', false);

        $this->_checking(Expectations::create()
            -> one($child)->charsetChanged('windows-874')
            -> ignoring($child)
            );

        $entity = $this->_createEntity($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );
        $entity->setChildren(array($child));
        $entity->charsetChanged('windows-874');
    }

    public function testEntityIsWrittenToByteStream()
    {
        $entity = $this->_createEntity($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );
        $is = $this->_createInputStream(false);
        $this->_checking(Expectations::create()
            -> atLeast(1)->of($is)->write(any())
            -> ignoring($is)
            );

        $entity->toByteStream($is);
    }

    public function testEntityHeadersAreComittedToByteStream()
    {
        $entity = $this->_createEntity($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );
        $is = $this->_createInputStream(false);
        $this->_checking(Expectations::create()
            -> atLeast(1)->of($is)->commit()
            -> atLeast(1)->of($is)->write(any())
            -> ignoring($is)
            );

        $entity->toByteStream($is);
    }

    public function testOrderingTextBeforeHtml()
    {
        $htmlChild = $this->_createChild(Swift_Mime_MimeEntity::LEVEL_ALTERNATIVE,
            "Content-Type: text/html\r\n" .
            "\r\n" .
            "HTML PART",
            false
            );
        $textChild = $this->_createChild(Swift_Mime_MimeEntity::LEVEL_ALTERNATIVE,
            "Content-Type: text/plain\r\n" .
            "\r\n" .
            "TEXT PART",
            false
            );
        $headers = $this->_createHeaderSet(array(), false);
        $this->_checking(Expectations::create()
            -> ignoring($headers)->toString() -> returns(
                "Content-Type: multipart/alternative; boundary=\"xxx\"\r\n"
                )
            -> ignoring($headers)
            -> ignoring($htmlChild)->getContentType() -> returns('text/html')
            -> ignoring($htmlChild)
            -> ignoring($textChild)->getContentType() -> returns('text/plain')
            -> ignoring($textChild)
            );
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $entity->setBoundary('xxx');
        $entity->setChildren(array($htmlChild, $textChild));

        $this->assertEqual(
            "Content-Type: multipart/alternative; boundary=\"xxx\"\r\n" .
            "\r\n\r\n--xxx\r\n" .
            "Content-Type: text/plain\r\n" .
            "\r\n" .
            "TEXT PART" .
            "\r\n\r\n--xxx\r\n" .
            "Content-Type: text/html\r\n" .
            "\r\n" .
            "HTML PART" .
            "\r\n\r\n--xxx--\r\n",
            $entity->toString()
            );
    }

    public function testUnsettingChildrenRestoresContentType()
    {
        $cType = $this->_createHeader('Content-Type', 'text/plain', array(), false);
        $child = $this->_createChild(Swift_Mime_MimeEntity::LEVEL_ALTERNATIVE);

        $s = $this->_mockery()->sequence('Type setting');
        $this->_checking(Expectations::create()
            -> one($cType)->setFieldBodyModel('image/jpeg') -> inSequence($s)
            -> one($cType)->setFieldBodyModel('multipart/alternative') -> inSequence($s)
            -> one($cType)->setFieldBodyModel('image/jpeg') -> inSequence($s)
            -> ignoring($cType)
            );

        $entity = $this->_createEntity($this->_createHeaderSet(array(
            'Content-Type' => $cType
            )),
            $this->_createEncoder(), $this->_createCache()
            );

        $entity->setContentType('image/jpeg');
        $entity->setChildren(array($child));
        $entity->setChildren(array());
    }

    public function testBodyIsReadFromCacheWhenUsingToStringIfPresent()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $this->_checking(Expectations::create()
            -> ignoring($headers)->toString() -> returns(
                "Content-Type: text/plain; charset=utf-8\r\n"
                )
            -> ignoring($headers)
            );

        $cache = $this->_createCache(false);
        $this->_checking(Expectations::create()
            -> one($cache)->hasKey(any(), 'body') -> returns(true)
            -> one($cache)->getString(any(), 'body') -> returns("\r\ncache\r\ncache!")
            -> ignoring($cache)
            );

        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $cache
            );

        $entity->setBody("blah\r\nblah!");
        $this->assertEqual(
            "Content-Type: text/plain; charset=utf-8\r\n" .
            "\r\n" .
            "cache\r\ncache!",
            $entity->toString()
            );
    }

    public function testBodyIsAddedToCacheWhenUsingToString()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $this->_checking(Expectations::create()
            -> ignoring($headers)->toString() -> returns(
                "Content-Type: text/plain; charset=utf-8\r\n"
                )
            -> ignoring($headers)
            );

        $cache = $this->_createCache(false);
        $this->_checking(Expectations::create()
            -> one($cache)->hasKey(any(), 'body') -> returns(false)
            -> one($cache)->setString(any(), 'body', "\r\nblah\r\nblah!", Swift_KeyCache::MODE_WRITE)
            -> ignoring($cache)
            );

        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $cache
            );

        $entity->setBody("blah\r\nblah!");
        $entity->toString();
    }

    public function testBodyIsClearedFromCacheIfNewBodySet()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $this->_checking(Expectations::create()
            -> ignoring($headers)->toString() -> returns(
                "Content-Type: text/plain; charset=utf-8\r\n"
                )
            -> ignoring($headers)
            );

        $cache = $this->_createCache(false);
        $this->_checking(Expectations::create()
            -> one($cache)->clearKey(any(), 'body')
            -> ignoring($cache)
            );

        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $cache
            );

        $entity->setBody("blah\r\nblah!");
        $entity->toString();

        $entity->setBody("new\r\nnew!");
    }

    public function testBodyIsNotClearedFromCacheIfSameBodySet()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $this->_checking(Expectations::create()
            -> ignoring($headers)->toString() -> returns(
                "Content-Type: text/plain; charset=utf-8\r\n"
                )
            -> ignoring($headers)
            );

        $cache = $this->_createCache(false);
        $this->_checking(Expectations::create()
            -> never($cache)->clearKey(any(), 'body')
            -> ignoring($cache)
            );

        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $cache
            );

        $entity->setBody("blah\r\nblah!");
        $entity->toString();

        $entity->setBody("blah\r\nblah!");
    }

    public function testBodyIsClearedFromCacheIfNewEncoderSet()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $this->_checking(Expectations::create()
            -> ignoring($headers)->toString() -> returns(
                "Content-Type: text/plain; charset=utf-8\r\n"
                )
            -> ignoring($headers)
            );

        $cache = $this->_createCache(false);
        $this->_checking(Expectations::create()
            -> one($cache)->clearKey(any(), 'body')
            -> ignoring($cache)
            );

        $otherEncoder = $this->_createEncoder();

        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $cache
            );

        $entity->setBody("blah\r\nblah!");
        $entity->toString();

        $entity->setEncoder($otherEncoder);
    }

    public function testBodyIsReadFromCacheWhenUsingToByteStreamIfPresent()
    {
        $is = $this->_createInputStream();
        $cache = $this->_createCache(false);

        $this->_checking(Expectations::create()
            -> one($cache)->hasKey(any(), 'body') -> returns(true)
            -> one($cache)->exportToByteStream(any(), 'body', $is)
            -> ignoring($cache)
            );

        $entity = $this->_createEntity($this->_createHeaderSet(),
            $this->_createEncoder(), $cache
            );
        $entity->setBody('foo');

        $entity->toByteStream($is);
    }

    public function testBodyIsAddedToCacheWhenUsingToByteStream()
    {
        $is = $this->_createInputStream();
        $cache = $this->_createCache(false);

        $this->_checking(Expectations::create()
            -> one($cache)->hasKey(any(), 'body') -> returns(false)
            //The input stream should be fetched for writing
            // Proving that it's actually written to is possible, but extremely
            // fragile.  Best let the acceptance tests cover this aspect
            -> one($cache)->getInputByteStream(any(), 'body')
            -> ignoring($cache)
            );

        $entity = $this->_createEntity($this->_createHeaderSet(),
            $this->_createEncoder(), $cache
            );
        $entity->setBody('foo');

        $entity->toByteStream($is);
    }

    public function testFluidInterface()
    {
        $entity = $this->_createEntity($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );

        $this->assertSame($entity,
            $entity
            ->setContentType('text/plain')
            ->setEncoder($this->_createEncoder())
            ->setId('foo@bar')
            ->setDescription('my description')
            ->setMaxLineLength(998)
            ->setBody('xx')
            ->setBoundary('xyz')
            ->setChildren(array())
            );
    }

    // -- Private helpers

    abstract protected function _createEntity($headers, $encoder, $cache);

    protected function _createChild($level = null, $string = '', $stub = true)
    {
        $child = $this->_mock('Swift_Mime_MimeEntity');
        if (isset($level)) {
            $this->_checking(Expectations::create()
                -> ignoring($child)->getNestingLevel() -> returns($level)
                );
        }
        $this->_checking(Expectations::create()
            -> ignoring($child)->toString() -> returns($string)
            );
        if ($stub) {
            $this->_checking(Expectations::create()
                -> ignoring($child)
                );
        }

        return $child;
    }

    protected function _createEncoder($name = 'quoted-printable', $stub = true)
    {
        $encoder = $this->_mock('Swift_Mime_ContentEncoder');
        $this->_checking(Expectations::create()
            -> ignoring($encoder)->getName() -> returns($name)
            );

        if ($stub) {
            $this->_checking(Expectations::create()
                -> ignoring($encoder)->encodeString(any(), optional())
                    -> calls(array($this, 'returnStringFromEncoder'))
                -> ignoring($encoder)
                );
        }

        return $encoder;
    }

    protected function _createCache($stub = true)
    {
        $cache = $this->_mock('Swift_KeyCache');

        if ($stub) {
            $this->_checking(Expectations::create()
                -> ignoring($cache)
                );
        }

        return $cache;
    }

    protected function _createHeaderSet($headers = array(), $stub = true)
    {
        $set = $this->_mock('Swift_Mime_HeaderSet');
        foreach ($headers as $key => $header) {
            $this->_checking(Expectations::create()
                -> ignoring($set)->has($key) -> returns(true)
                -> ignoring($set)->get($key) -> returns($header)
                );
        }
        if ($stub) {
            $this->_checking(Expectations::create()
                -> ignoring($set)->newInstance() -> returns($set)
                -> ignoring($set)
                );
        }

        return $set;
    }

    protected function _createHeader($name, $model = null, $params = array(), $stub = true)
    {
        $header = $this->_mock('Swift_Mime_ParameterizedHeader');
        $this->_checking(Expectations::create()
            -> ignoring($header)->getFieldName() -> returns($name)
            -> ignoring($header)->getFieldBodyModel() -> returns($model)
            );
        foreach ($params as $key => $value) {
            $this->_checking(Expectations::create()
                -> ignoring($header)->getParameter($key) -> returns($value)
                );
        }
        if ($stub) {
            $this->_checking(Expectations::create()
                -> ignoring($header)
                );
        }

        return $header;
    }

    protected function _createOutputStream($data = null, $stub = true)
    {
        $os = $this->_mock('Swift_OutputByteStream');
        if (isset($data)) {
            $pos = $this->_mockery()->states('position')->startsAs('at beginning');
            $this->_checking(Expectations::create()
                -> ignoring($os)->read(optional()) -> returns($data)
                    -> when($pos->isNot('at end')) -> then($pos->is('at end'))

                -> ignoring($os)->read(optional()) -> returns(false)
                );
            if ($stub) {
                $this->_checking(Expectations::create()
                    -> ignoring($os)
                    );
            }
        }

        return $os;
    }

    protected function _createInputStream($stub = true)
    {
        $is = $this->_mock('Swift_InputByteStream');
        if ($stub) {
            $this->_checking(Expectations::create()
                -> ignoring($is)
                );
        }

        return $is;
    }

    // -- Mock helpers

    public function returnStringFromEncoder(Yay_Invocation $invocation)
    {
        $args = $invocation->getArguments();

        return array_shift($args);
    }
}
