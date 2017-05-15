<?php

use Egulias\EmailValidator\EmailValidator;

class Swift_Mime_Headers_MailboxHeaderTest extends \SwiftMailerTestCase
{
    /* -- RFC 2822, 3.6.2 for all tests.
     */

    private $charset = 'utf-8';

    public function testTypeIsMailboxHeader()
    {
        $header = $this->getHeader('To', $this->getEncoder('Q', true));
        $this->assertEquals(Swift_Mime_Header::TYPE_MAILBOX, $header->getFieldType());
    }

    public function testMailboxIsSetForAddress()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setAddresses('chris@swiftmailer.org');
        $this->assertEquals(array('chris@swiftmailer.org'),
            $header->getNameAddressStrings()
            );
    }

    public function testMailboxIsRenderedForNameAddress()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setNameAddresses(array('chris@swiftmailer.org' => 'Chris Corbyn'));
        $this->assertEquals(
            array('Chris Corbyn <chris@swiftmailer.org>'), $header->getNameAddressStrings()
            );
    }

    public function testAddressCanBeReturnedForAddress()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setAddresses('chris@swiftmailer.org');
        $this->assertEquals(array('chris@swiftmailer.org'), $header->getAddresses());
    }

    public function testAddressCanBeReturnedForNameAddress()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setNameAddresses(array('chris@swiftmailer.org' => 'Chris Corbyn'));
        $this->assertEquals(array('chris@swiftmailer.org'), $header->getAddresses());
    }

    public function testQuotesInNameAreQuoted()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setNameAddresses(array(
            'chris@swiftmailer.org' => 'Chris Corbyn, "DHE"',
            ));
        $this->assertEquals(
            array('"Chris Corbyn, \"DHE\"" <chris@swiftmailer.org>'),
            $header->getNameAddressStrings()
            );
    }

    public function testEscapeCharsInNameAreQuoted()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setNameAddresses(array(
            'chris@swiftmailer.org' => 'Chris Corbyn, \\escaped\\',
            ));
        $this->assertEquals(
            array('"Chris Corbyn, \\\\escaped\\\\" <chris@swiftmailer.org>'),
            $header->getNameAddressStrings()
            );
    }

    public function testGetMailboxesReturnsNameValuePairs()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setNameAddresses(array(
            'chris@swiftmailer.org' => 'Chris Corbyn, DHE',
            ));
        $this->assertEquals(
            array('chris@swiftmailer.org' => 'Chris Corbyn, DHE'), $header->getNameAddresses()
            );
    }

    public function testMultipleAddressesCanBeSetAndFetched()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setAddresses(array(
            'chris@swiftmailer.org', 'mark@swiftmailer.org',
            ));
        $this->assertEquals(
            array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
            $header->getAddresses()
            );
    }

    public function testMultipleAddressesAsMailboxes()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setAddresses(array(
            'chris@swiftmailer.org', 'mark@swiftmailer.org',
            ));
        $this->assertEquals(
            array('chris@swiftmailer.org' => null, 'mark@swiftmailer.org' => null),
            $header->getNameAddresses()
            );
    }

    public function testMultipleAddressesAsMailboxStrings()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setAddresses(array(
            'chris@swiftmailer.org', 'mark@swiftmailer.org',
            ));
        $this->assertEquals(
            array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
            $header->getNameAddressStrings()
            );
    }

    public function testMultipleNamedMailboxesReturnsMultipleAddresses()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setNameAddresses(array(
            'chris@swiftmailer.org' => 'Chris Corbyn',
            'mark@swiftmailer.org' => 'Mark Corbyn',
            ));
        $this->assertEquals(
            array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
            $header->getAddresses()
            );
    }

    public function testMultipleNamedMailboxesReturnsMultipleMailboxes()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setNameAddresses(array(
            'chris@swiftmailer.org' => 'Chris Corbyn',
            'mark@swiftmailer.org' => 'Mark Corbyn',
            ));
        $this->assertEquals(array(
                'chris@swiftmailer.org' => 'Chris Corbyn',
                'mark@swiftmailer.org' => 'Mark Corbyn',
                ),
            $header->getNameAddresses()
            );
    }

    public function testMultipleMailboxesProducesMultipleMailboxStrings()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setNameAddresses(array(
            'chris@swiftmailer.org' => 'Chris Corbyn',
            'mark@swiftmailer.org' => 'Mark Corbyn',
            ));
        $this->assertEquals(array(
                'Chris Corbyn <chris@swiftmailer.org>',
                'Mark Corbyn <mark@swiftmailer.org>',
                ),
            $header->getNameAddressStrings()
            );
    }

    public function testSetAddressesOverwritesAnyMailboxes()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setNameAddresses(array(
            'chris@swiftmailer.org' => 'Chris Corbyn',
            'mark@swiftmailer.org' => 'Mark Corbyn',
            ));
        $this->assertEquals(
            array('chris@swiftmailer.org' => 'Chris Corbyn',
            'mark@swiftmailer.org' => 'Mark Corbyn', ),
            $header->getNameAddresses()
            );
        $this->assertEquals(
            array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
            $header->getAddresses()
            );

        $header->setAddresses(array('chris@swiftmailer.org', 'mark@swiftmailer.org'));

        $this->assertEquals(
            array('chris@swiftmailer.org' => null, 'mark@swiftmailer.org' => null),
            $header->getNameAddresses()
            );
        $this->assertEquals(
            array('chris@swiftmailer.org', 'mark@swiftmailer.org'),
            $header->getAddresses()
            );
    }

    public function testNameIsEncodedIfNonAscii()
    {
        $name = 'C'.pack('C', 0x8F).'rbyn';

        $encoder = $this->getEncoder('Q');
        $encoder->shouldReceive('encodeString')
                ->once()
                ->with($name, \Mockery::any(), \Mockery::any(), \Mockery::any())
                ->andReturn('C=8Frbyn');

        $header = $this->getHeader('From', $encoder);
        $header->setNameAddresses(array('chris@swiftmailer.org' => 'Chris '.$name));

        $addresses = $header->getNameAddressStrings();
        $this->assertEquals(
            'Chris =?'.$this->charset.'?Q?C=8Frbyn?= <chris@swiftmailer.org>',
            array_shift($addresses)
            );
    }

    public function testEncodingLineLengthCalculations()
    {
        /* -- RFC 2047, 2.
        An 'encoded-word' may not be more than 75 characters long, including
        'charset', 'encoding', 'encoded-text', and delimiters.
        */

        $name = 'C'.pack('C', 0x8F).'rbyn';

        $encoder = $this->getEncoder('Q');
        $encoder->shouldReceive('encodeString')
                ->once()
                ->with($name, \Mockery::any(), \Mockery::any(), \Mockery::any())
                ->andReturn('C=8Frbyn');

        $header = $this->getHeader('From', $encoder);
        $header->setNameAddresses(array('chris@swiftmailer.org' => 'Chris '.$name));

        $header->getNameAddressStrings();
    }

    public function testGetValueReturnsMailboxStringValue()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setNameAddresses(array(
            'chris@swiftmailer.org' => 'Chris Corbyn',
            ));
        $this->assertEquals(
            'Chris Corbyn <chris@swiftmailer.org>', $header->getFieldBody()
            );
    }

    public function testGetValueReturnsMailboxStringValueForMultipleMailboxes()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setNameAddresses(array(
            'chris@swiftmailer.org' => 'Chris Corbyn',
            'mark@swiftmailer.org' => 'Mark Corbyn',
            ));
        $this->assertEquals(
            'Chris Corbyn <chris@swiftmailer.org>, Mark Corbyn <mark@swiftmailer.org>',
            $header->getFieldBody()
            );
    }

    public function testRemoveAddressesWithSingleValue()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setNameAddresses(array(
            'chris@swiftmailer.org' => 'Chris Corbyn',
            'mark@swiftmailer.org' => 'Mark Corbyn',
            ));
        $header->removeAddresses('chris@swiftmailer.org');
        $this->assertEquals(array('mark@swiftmailer.org'),
            $header->getAddresses()
            );
    }

    public function testRemoveAddressesWithList()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setNameAddresses(array(
            'chris@swiftmailer.org' => 'Chris Corbyn',
            'mark@swiftmailer.org' => 'Mark Corbyn',
            ));
        $header->removeAddresses(
            array('chris@swiftmailer.org', 'mark@swiftmailer.org')
            );
        $this->assertEquals(array(), $header->getAddresses());
    }

    public function testSetBodyModel()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setFieldBodyModel('chris@swiftmailer.org');
        $this->assertEquals(array('chris@swiftmailer.org' => null), $header->getNameAddresses());
    }

    public function testGetBodyModel()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setAddresses(array('chris@swiftmailer.org'));
        $this->assertEquals(array('chris@swiftmailer.org' => null), $header->getFieldBodyModel());
    }

    public function testToString()
    {
        $header = $this->getHeader('From', $this->getEncoder('Q', true));
        $header->setNameAddresses(array(
            'chris@swiftmailer.org' => 'Chris Corbyn',
            'mark@swiftmailer.org' => 'Mark Corbyn',
            ));
        $this->assertEquals(
            'From: Chris Corbyn <chris@swiftmailer.org>, '.
            'Mark Corbyn <mark@swiftmailer.org>'."\r\n",
            $header->toString()
            );
    }

    private function getHeader($name, $encoder)
    {
        $header = new Swift_Mime_Headers_MailboxHeader($name, $encoder, new EmailValidator());
        $header->setCharset($this->charset);

        return $header;
    }

    private function getEncoder($type, $stub = false)
    {
        $encoder = $this->getMockery('Swift_Mime_HeaderEncoder')->shouldIgnoreMissing();
        $encoder->shouldReceive('getName')
                ->zeroOrMoreTimes()
                ->andReturn($type);

        return $encoder;
    }
}
