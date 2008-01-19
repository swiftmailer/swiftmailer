<?php

require_once 'Swift/AbstractSwiftUnitTestCase.php';
require_once 'Swift/Mime/HeaderAttribute/SimpleHeaderAttribute.php';
require_once 'Swift/Encoder.php';

Mock::generate('Swift_Encoder', 'Swift_MockEncoder');

class Swift_Mime_HeaderAttribute_SimpleHeaderAttributeTest
  extends Swift_AbstractSwiftUnitTestCase
{

  private $_charset = 'utf-8';
  private $_lang = 'en-us';
  
  public function testGetNameReturnsNameVerbatim()
  {
    $attribute = $this->_getAttribute('charset', 'utf-8');
    $this->assertEqual('charset', $attribute->getName());
  }
  
  public function testValueIsReturnedVerbatim()
  {
    $attribute = $this->_getAttribute('charset', 'utf-8');
    $this->assertEqual('utf-8', $attribute->getValue());
  }
  
  public function testToStringReturnsAttributeValuePair()
  {
    /* -- RFC 2045, 5.1
    parameter := attribute "=" value

     attribute := token
                  ; Matching of attributes
                  ; is ALWAYS case-insensitive.

     value := token / quoted-string

     token := 1*<any (US-ASCII) CHAR except SPACE, CTLs,
                 or tspecials>

     tspecials :=  "(" / ")" / "<" / ">" / "@" /
                   "," / ";" / ":" / "\" / <">
                   "/" / "[" / "]" / "?" / "="
                   ; Must be in quoted-string,
                   ; to use within parameter values
    */
    
    $attribute = $this->_getAttribute('charset', 'utf-8');
    $this->assertEqual('charset=utf-8', $attribute->toString());
  }
  
  public function testSpaceResultsInQuotedString()
  {
    $attribute = $this->_getAttribute('filename', 'my file.txt');
    $this->assertEqual('my file.txt', $attribute->getValue());
    $this->assertEqual('filename="my file.txt"', $attribute->toString());
  }
  
  public function testLongLinesAreBrokenIntoMultipleAttributeStrings()
  {
    /* -- RFC 2231, 3.
    The asterisk character ("*") followed
    by a decimal count is employed to indicate that multiple parameters
    are being used to encapsulate a single parameter value.  The count
    starts at 0 and increments by 1 for each subsequent section of the
    parameter value.  Decimal values are used and neither leading zeroes
    nor gaps in the sequence are allowed.

    The original parameter value is recovered by concatenating the
    various sections of the parameter, in order.  For example, the
    content-type field

        Content-Type: message/external-body; access-type=URL;
         URL*0="ftp://";
         URL*1="cs.utk.edu/pub/moore/bulk-mailer/bulk-mailer.tar"

    is semantically identical to

        Content-Type: message/external-body; access-type=URL;
          URL="ftp://cs.utk.edu/pub/moore/bulk-mailer/bulk-mailer.tar"

    Note that quotes around parameter values are part of the value
    syntax; they are NOT part of the value itself.  Furthermore, it is
    explicitly permitted to have a mixture of quoted and unquoted
    continuation fields.
    */
    
    $value = str_repeat('a', 180);
    
    $encoder = new Swift_MockEncoder();
    $encoder->expectOnce('encodeString', array($value, 64, '*'));
    $encoder->setReturnValue('encodeString', str_repeat('a', 64) . "\r\n" .
      str_repeat('a', 64) . "\r\n" . str_repeat('a', 52)
      );
    
    $attribute = $this->_getAttribute('filename', $value, $encoder);
    $attribute->setMaxLineLength(77);
    $this->assertEqual($value, $attribute->getValue());
    $this->assertEqual(
      'filename*0=' . str_repeat('a', 64) . "\r\n" .
      'filename*1=' . str_repeat('a', 64) . "\r\n" .
      'filename*2=' . str_repeat('a', 52),
      $attribute->toString()
      );
  }
  
  public function testEncodedDataIncludesCharsetAndLanguage()
  {
    /* -- RFC 2231, 4.
    Asterisks ("*") are reused to provide the indicator that language and
    character set information is present and encoding is being used. A
    single quote ("'") is used to delimit the character set and language
    information at the beginning of the parameter value. Percent signs
    ("%") are used as the encoding flag, which agrees with RFC 2047.

    Specifically, an asterisk at the end of a parameter name acts as an
    indicator that character set and language information may appear at
    the beginning of the parameter value. A single quote is used to
    separate the character set, language, and actual value information in
    the parameter value string, and an percent sign is used to flag
    octets encoded in hexadecimal.  For example:

        Content-Type: application/x-stuff;
         title*=us-ascii'en-us'This%20is%20%2A%2A%2Afun%2A%2A%2A

    Note that it is perfectly permissible to leave either the character
    set or language field blank.  Note also that the single quote
    delimiters MUST be present even when one of the field values is
    omitted.
    */
    
    $value = str_repeat('a', 20) . pack('C', 0x8F) . str_repeat('a', 10);
    
    $encoder = new Swift_MockEncoder();
    $encoder->expectOnce('encodeString', array(
      new Swift_IdenticalBinaryExpectation($value), 63, 12
      ));
    $encoder->setReturnValue('encodeString', str_repeat('a', 20) . '%8F' .
      str_repeat('a', 10)
      );
    
    $attribute = $this->_getAttribute('filename', $value, $encoder);
    $attribute->setMaxLineLength(77);
    $this->assertEqual($value, $attribute->getValue());
    $this->assertEqual(
      "filename*=" . $this->_charset . "'" . $this->_lang . "'" .
      str_repeat('a', 20) . '%8F' . str_repeat('a', 10),
      $attribute->toString()
      );
  }
  
  public function testMultipleEncodedLinesAreFormattedCorrectly()
  {
    /* -- RFC 2231, 4.1.
    Character set and language information may be combined with the
    parameter continuation mechanism. For example:

    Content-Type: application/x-stuff
     title*0*=us-ascii'en'This%20is%20even%20more%20
     title*1*=%2A%2A%2Afun%2A%2A%2A%20
     title*2="isn't it!"

    Note that:

     (1)   Language and character set information only appear at
           the beginning of a given parameter value.

     (2)   Continuations do not provide a facility for using more
           than one character set or language in the same
           parameter value.

     (3)   A value presented using multiple continuations may
           contain a mixture of encoded and unencoded segments.

     (4)   The first segment of a continuation MUST be encoded if
           language and character set information are given.

     (5)   If the first segment of a continued parameter value is
           encoded the language and character set field delimiters
           MUST be present even when the fields are left blank.
    */
          
     $value = str_repeat('a', 20) . pack('C', 0x8F) . str_repeat('a', 60);
    
    $encoder = new Swift_MockEncoder();
    $encoder->expectOnce('encodeString', array(
      new Swift_IdenticalBinaryExpectation($value), 63, 12
      ));
    $encoder->setReturnValue('encodeString', str_repeat('a', 20) . '%8F' .
      str_repeat('a', 28) . "\r\n" . str_repeat('a', 32)
      );
    
    $attribute = $this->_getAttribute('filename', $value, $encoder);
    $attribute->setMaxLineLength(77);
    $this->assertEqual($value, $attribute->getValue());
    $this->assertEqual(
      "filename*0*=" . $this->_charset . "'" . $this->_lang . "'" .
      str_repeat('a', 20) . '%8F' . str_repeat('a', 28) . "\r\n" .
      'filename*1=' . str_repeat('a', 32),
      $attribute->toString()
      );     
  }
  
  // -- Private methods
  
  private function _getAttribute($name, $value, $encoder = null)
  {
    return new Swift_Mime_HeaderAttribute_SimpleHeaderAttribute(
      $name, $value, $this->_charset, $this->_lang, $encoder
      );
  }
  
}
