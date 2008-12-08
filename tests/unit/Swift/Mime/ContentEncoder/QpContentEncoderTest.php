<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Mime/ContentEncoder/QpContentEncoder.php';
require_once 'Swift/InputByteStream.php';
require_once 'Swift/OutputByteStream.php';
require_once 'Swift/CharacterStream.php';

class Swift_StreamCollector implements Yay_Action {
  public $content = '';
  public function &invoke(Yay_Invocation $inv) {
    $args = $inv->getArguments();
    $this->content .= current($args);
  }
  public function describeTo(Yay_Description $d) {
    $description->appendText(' gathers input;');
  }
}

class Swift_MockInputByteStream implements Swift_InputByteStream {
  public $content = '';
  public function write($chars, Swift_InputByteStream $is = null) {
    $this->content .= $chars;
  }
  public function flushBuffers() {
  }
}

Mock::generate('Swift_OutputByteStream', 'Swift_MockOutputByteStream');
Mock::generate('Swift_CharacterStream', 'Swift_MockCharacterStream');

class Swift_Mime_ContentEncoder_QpContentEncoderTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testNameIsQuotedPrintable()
  {
    $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder(
      new Swift_MockCharacterStream()
      );
    $this->assertEqual('quoted-printable', $encoder->getName());
  }
  
  /* -- RFC 2045, 6.7 --
  (1)   (General 8bit representation) Any octet, except a CR or
          LF that is part of a CRLF line break of the canonical
          (standard) form of the data being encoded, may be
          represented by an "=" followed by a two digit
          hexadecimal representation of the octet's value.  The
          digits of the hexadecimal alphabet, for this purpose,
          are "0123456789ABCDEF".  Uppercase letters must be
          used; lowercase letters are not allowed.  Thus, for
          example, the decimal value 12 (US-ASCII form feed) can
          be represented by "=0C", and the decimal value 61 (US-
          ASCII EQUAL SIGN) can be represented by "=3D".  This
          rule must be followed except when the following rules
          allow an alternative encoding.
          */
  
  public function testPermittedCharactersAreNotEncoded()
  {
    /* -- RFC 2045, 6.7 --
    (2)   (Literal representation) Octets with decimal values of
          33 through 60 inclusive, and 62 through 126, inclusive,
          MAY be represented as the US-ASCII characters which
          correspond to those octets (EXCLAMATION POINT through
          LESS THAN, and GREATER THAN through TILDE,
          respectively).
          */
    
    foreach (array_merge(range(33, 60), range(62, 126)) as $ordinal)
    { 
      $char = chr($ordinal);
      
      $os = new Swift_MockOutputByteStream();
      
      $charStream = new Swift_MockCharacterStream();
      $charStream->expectOnce('flushContents');
      $charStream->expectOnce('importByteStream', array($os));
      
      $charStream->setReturnValueAt(0, 'readBytes', array($ordinal));
      $charStream->setReturnValueAt(1, 'readBytes', false);
      
      $is = new Swift_MockInputByteStream();
      $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
      $encoder->encodeByteStream($os, $is);
      $this->assertIdenticalBinary($char, $is->content);
    }
  }
  
  public function testLinearWhiteSpaceAtLineEndingIsEncoded()
  {
    /* -- RFC 2045, 6.7 --
    (3)   (White Space) Octets with values of 9 and 32 MAY be
          represented as US-ASCII TAB (HT) and SPACE characters,
          respectively, but MUST NOT be so represented at the end
          of an encoded line.  Any TAB (HT) or SPACE characters
          on an encoded line MUST thus be followed on that line
          by a printable character.  In particular, an "=" at the
          end of an encoded line, indicating a soft line break
          (see rule #5) may follow one or more TAB (HT) or SPACE
          characters.  It follows that an octet with decimal
          value 9 or 32 appearing at the end of an encoded line
          must be represented according to Rule #1.  This rule is
          necessary because some MTAs (Message Transport Agents,
          programs which transport messages from one user to
          another, or perform a portion of such transfers) are
          known to pad lines of text with SPACEs, and others are
          known to remove "white space" characters from the end
          of a line.  Therefore, when decoding a Quoted-Printable
          body, any trailing white space on a line must be
          deleted, as it will necessarily have been added by
          intermediate transport agents.
          */
    
    $HT = chr(0x09); //9
    $SPACE = chr(0x20); //32
    
    //HT
    $os = new Swift_MockOutputByteStream();
    
    $charStream = new Swift_MockCharacterStream();
    $charStream->expectOnce('flushContents');
    $charStream->expectOnce('importByteStream', array($os));
    $charStream->setReturnValueAt(0, 'readBytes', array(ord('a')));
    $charStream->setReturnValueAt(1, 'readBytes', array(0x09));
    $charStream->setReturnValueAt(2, 'readBytes', array(0x09));
    $charStream->setReturnValueAt(3, 'readBytes', array(0x0D));
    $charStream->setReturnValueAt(4, 'readBytes', array(0x0A));
    $charStream->setReturnValueAt(5, 'readBytes', array(ord('b')));
    $charStream->setReturnValueAt(6, 'readBytes', false);
    
    $is = new Swift_MockInputByteStream();
    
    $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
    $encoder->encodeByteStream($os, $is);
    
    $this->assertEqual("a\t=09\r\nb", $is->content);
    
    //SPACE
    $os = new Swift_MockOutputByteStream();
    
    $charStream = new Swift_MockCharacterStream();
    $charStream->expectOnce('flushContents');
    $charStream->expectOnce('importByteStream', array($os));
    $charStream->setReturnValueAt(0, 'readBytes', array(ord('a')));
    $charStream->setReturnValueAt(1, 'readBytes', array(0x20));
    $charStream->setReturnValueAt(2, 'readBytes', array(0x20));
    $charStream->setReturnValueAt(3, 'readBytes', array(0x0D));
    $charStream->setReturnValueAt(4, 'readBytes', array(0x0A));
    $charStream->setReturnValueAt(5, 'readBytes', array(ord('b')));
    $charStream->setReturnValueAt(6, 'readBytes', false);
    
    $is = new Swift_MockInputByteStream();
    
    $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
    $encoder->encodeByteStream($os, $is);
    
    $this->assertEqual("a =20\r\nb", $is->content);
  }
  
  public function testCRLFIsLeftAlone()
  {
    /*
    (4)   (Line Breaks) A line break in a text body, represented
          as a CRLF sequence in the text canonical form, must be
          represented by a (RFC 822) line break, which is also a
          CRLF sequence, in the Quoted-Printable encoding.  Since
          the canonical representation of media types other than
          text do not generally include the representation of
          line breaks as CRLF sequences, no hard line breaks
          (i.e. line breaks that are intended to be meaningful
          and to be displayed to the user) can occur in the
          quoted-printable encoding of such types.  Sequences
          like "=0D", "=0A", "=0A=0D" and "=0D=0A" will routinely
          appear in non-text data represented in quoted-
          printable, of course.

          Note that many implementations may elect to encode the
          local representation of various content types directly
          rather than converting to canonical form first,
          encoding, and then converting back to local
          representation.  In particular, this may apply to plain
          text material on systems that use newline conventions
          other than a CRLF terminator sequence.  Such an
          implementation optimization is permissible, but only
          when the combined canonicalization-encoding step is
          equivalent to performing the three steps separately.
          */
    
    $os = new Swift_MockOutputByteStream();
    
    $charStream = new Swift_MockCharacterStream();
    $charStream->expectOnce('flushContents');
    $charStream->expectOnce('importByteStream', array($os));
    $charStream->setReturnValueAt(0, 'readBytes', array(ord('a')));
    $charStream->setReturnValueAt(1, 'readBytes', array(0x0D));
    $charStream->setReturnValueAt(2, 'readBytes', array(0x0A));
    $charStream->setReturnValueAt(3, 'readBytes', array(ord('b')));
    $charStream->setReturnValueAt(4, 'readBytes', array(0x0D));
    $charStream->setReturnValueAt(5, 'readBytes', array(0x0A));
    $charStream->setReturnValueAt(6, 'readBytes', array(ord('c')));
    $charStream->setReturnValueAt(7, 'readBytes', array(0x0D));
    $charStream->setReturnValueAt(8, 'readBytes', array(0x0A));
    $charStream->setReturnValueAt(9, 'readBytes', false);
    
    $is = new Swift_MockInputByteStream();
    
    $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
    $encoder->encodeByteStream($os, $is);
    $this->assertEqual("a\r\nb\r\nc\r\n", $is->content);
  }
  
  public function testLinesLongerThan76CharactersAreSoftBroken()
  {
    /*
    (5)   (Soft Line Breaks) The Quoted-Printable encoding
          REQUIRES that encoded lines be no more than 76
          characters long.  If longer lines are to be encoded
          with the Quoted-Printable encoding, "soft" line breaks
          must be used.  An equal sign as the last character on a
          encoded line indicates such a non-significant ("soft")
          line break in the encoded text.
          */
    
    $os = new Swift_MockOutputByteStream();
    
    $charStream = new Swift_MockCharacterStream();
    $charStream->expectOnce('flushContents');
    $charStream->expectOnce('importByteStream', array($os));
    
    $is = new Swift_MockInputByteStream();
    
    $seq = 0;
    for (; $seq <= 140; ++$seq)
    {
      $charStream->setReturnValueAt($seq, 'readBytes', array(ord('a')));
    }
    $charStream->setReturnValueAt($seq, 'readBytes', false);
    
    $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
    $encoder->encodeByteStream($os, $is);
    $this->assertEqual(str_repeat('a', 75) . "=\r\n" . str_repeat('a', 66), $is->content);
  }
  
  public function testMaxLineLengthCanBeSpecified()
  {
    $os = new Swift_MockOutputByteStream();
    
    $charStream = new Swift_MockCharacterStream();
    $charStream->expectOnce('flushContents');
    $charStream->expectOnce('importByteStream', array($os));
    
    $is = new Swift_MockInputByteStream();
    
    $seq = 0;
    for (; $seq <= 100; ++$seq)
    {
      $charStream->setReturnValueAt($seq, 'readBytes', array(ord('a')));
    }
    $charStream->setReturnValueAt($seq, 'readBytes', false);
    
    $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
    $encoder->encodeByteStream($os, $is, 0, 54);
    $this->assertEqual(str_repeat('a', 53) . "=\r\n" . str_repeat('a', 48), $is->content);
  }
  
  public function testBytesBelowPermittedRangeAreEncoded()
  {
    /*
    According to Rule (1 & 2)
    */
    
    foreach (range(0, 32) as $ordinal)
    { 
      $char = chr($ordinal);
      
      $os = new Swift_MockOutputByteStream();
      
      $charStream = new Swift_MockCharacterStream();
      $charStream->expectOnce('flushContents');
      $charStream->expectOnce('importByteStream', array($os));
      
      $charStream->setReturnValueAt(0, 'readBytes', array($ordinal));
      $charStream->setReturnValueAt(1, 'readBytes', false);
      
      $is = new Swift_MockInputByteStream();
      $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
      $encoder->encodeByteStream($os, $is);
      $this->assertEqual(sprintf('=%02X', $ordinal), $is->content);
    }
  }
  
  public function testDecimalByte61IsEncoded()
  {
    /*
    According to Rule (1 & 2)
    */
    
    $char = chr(61);
    
    $os = new Swift_MockOutputByteStream();
      
    $charStream = new Swift_MockCharacterStream();
    $charStream->expectOnce('flushContents');
    $charStream->expectOnce('importByteStream', array($os));
      
    $charStream->setReturnValueAt(0, 'readBytes', array(61));
    $charStream->setReturnValueAt(1, 'readBytes', false);
      
    $is = new Swift_MockInputByteStream();
    $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
    $encoder->encodeByteStream($os, $is);
    $this->assertEqual(sprintf('=%02X', 61), $is->content);
  }
  
  public function testBytesAbovePermittedRangeAreEncoded()
  {
    /*
    According to Rule (1 & 2)
    */
    
    foreach (range(127, 255) as $ordinal)
    { 
      $char = chr($ordinal);
      
      $os = new Swift_MockOutputByteStream();
      
      $charStream = new Swift_MockCharacterStream();
      $charStream->expectOnce('flushContents');
      $charStream->expectOnce('importByteStream', array($os));
      
      $charStream->setReturnValueAt(0, 'readBytes', array($ordinal));
      $charStream->setReturnValueAt(1, 'readBytes', false);
      
      $is = new Swift_MockInputByteStream();
      $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
      $encoder->encodeByteStream($os, $is);
      $this->assertEqual(sprintf('=%02X', $ordinal), $is->content);
    }
  }
  
  public function testFirstLineLengthCanBeDifferent()
  {
    $os = new Swift_MockOutputByteStream();
    
    $charStream = new Swift_MockCharacterStream();
    $charStream->expectOnce('flushContents');
    $charStream->expectOnce('importByteStream', array($os));
    
    $is = new Swift_MockInputByteStream();
    
    $seq = 0;
    for (; $seq <= 140; ++$seq)
    {
      $charStream->setReturnValueAt($seq, 'readBytes', array(ord('a')));
    }
    $charStream->setReturnValueAt($seq, 'readBytes', false);
    
    $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
    $encoder->encodeByteStream($os, $is, 22);
    $this->assertEqual(
      str_repeat('a', 53) . "=\r\n" . str_repeat('a', 75) . "=\r\n" . str_repeat('a', 13),
      $is->content
      );
  }
  
  public function testCanonicEncodeStringGeneratesCorrectCrlf_1()
  {
    $charStream = new Swift_MockCharacterStream();
    $charStream->setReturnValueAt(0, 'readBytes', array(ord('a')));
    $charStream->setReturnValueAt(1, 'readBytes', array(0x0D));
    $charStream->setReturnValueAt(2, 'readBytes', array(ord('b')));
    $charStream->setReturnValueAt(3, 'readBytes', false);
    $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream, true);
    
    $this->assertEqual("a\r\nb", $encoder->encodeString("a\rb"),
      '%s: Input should be canonicalized to CRLF endings'
      );
  }
  
  public function testCanonicEncodeStringGeneratesCorrectCrlf_2()
  {
    $charStream = new Swift_MockCharacterStream();
    $charStream->setReturnValueAt(0, 'readBytes', array(ord('a')));
    $charStream->setReturnValueAt(1, 'readBytes', array(0x0D));
    $charStream->setReturnValueAt(2, 'readBytes', array(ord('b')));
    $charStream->setReturnValueAt(3, 'readBytes', false);
    $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream, true);
    
    $this->assertEqual("a\r\nb", $encoder->encodeString("a\nb"),
      '%s: Input should be canonicalized to CRLF endings'
      );
  }
  
  public function testCanonicEncodeStringGeneratesCorrectCrlf_3()
  {
    $charStream = new Swift_MockCharacterStream();
    $charStream->setReturnValueAt(0, 'readBytes', array(ord('a')));
    $charStream->setReturnValueAt(1, 'readBytes', array(0x0A));
    $charStream->setReturnValueAt(2, 'readBytes', array(0x0D));
    $charStream->setReturnValueAt(3, 'readBytes', array(ord('b')));
    $charStream->setReturnValueAt(4, 'readBytes', false);
    $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream, true);
    
    $this->assertEqual("a\r\n\r\nb", $encoder->encodeString("a\n\rb"),
      '%s: Input should be canonicalized to CRLF endings'
      );
  }
  
  public function testCanonicEncodeStringGeneratesCorrectCrlf_4()
  {
    $charStream = new Swift_MockCharacterStream();
    $charStream->setReturnValueAt(0, 'readBytes', array(ord('a')));
    $charStream->setReturnValueAt(1, 'readBytes', array(0x0A));
    $charStream->setReturnValueAt(2, 'readBytes', array(0x0A));
    $charStream->setReturnValueAt(3, 'readBytes', array(ord('b')));
    $charStream->setReturnValueAt(4, 'readBytes', false);
    $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream, true);
    
    $this->assertEqual("a\r\n\r\nb", $encoder->encodeString("a\n\nb"),
      '%s: Input should be canonicalized to CRLF endings'
      );
  }
  
  public function testCanonicEncodeStringGeneratesCorrectCrlf_5()
  {
    $charStream = new Swift_MockCharacterStream();
    $charStream->setReturnValueAt(0, 'readBytes', array(ord('a')));
    $charStream->setReturnValueAt(1, 'readBytes', array(0x0D));
    $charStream->setReturnValueAt(2, 'readBytes', array(0x0D));
    $charStream->setReturnValueAt(3, 'readBytes', array(ord('b')));
    $charStream->setReturnValueAt(4, 'readBytes', false);
    $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream, true);
    
    $this->assertEqual("a\r\n\r\nb", $encoder->encodeString("a\r\rb"),
      '%s: Input should be canonicalized to CRLF endings'
      );
  }
  
  public function testObserverInterfaceCanChangeCharset()
  {
    $stream = $this->_mock('Swift_CharacterStream');
    $this->_checking(Expectations::create()
      -> one($stream)->setCharacterSet('windows-1252')
      -> ignoring($stream)
      );
    $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($stream);
    $encoder->charsetChanged('windows-1252');

  }
  
}
