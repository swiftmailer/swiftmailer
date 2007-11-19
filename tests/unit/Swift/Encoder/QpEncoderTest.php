<?php

require_once 'Swift/Encoder/QpEncoder.php';

class Swift_Encoder_QpEncoderTest extends UnitTestCase
{
  
  private $_encoder;
  
  public function setUp()
  {
    $this->_encoder = new Swift_Encoder_QpEncoder();
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
    
    for ($ordinal = 33; $ordinal <= 126; ++$ordinal)
    {
      if (61 == $ordinal)
      {
        continue;
      }
      $char = chr($ordinal);
      $this->assertIdentical($char, $this->_encoder->encodeString($char));
    }
  }
  
  public function testWhiteSpaceAtLineEndingIsEncoded()
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
    
    $string = 'foo' . $HT . $HT . $HT . "\r\n" . 'bar';
    
    $this->assertEqual(
      'foo' . $HT . $HT . '=09' . "\r\n" . 'bar',
      $this->_encoder->encodeString($string)
      );
    
    $string = 'foo' . $SPACE . $SPACE . $SPACE . "\r\n" . 'bar';
    
    $this->assertEqual(
      'foo' . $SPACE . $SPACE . '=20' . "\r\n" . 'bar',
      $this->_encoder->encodeString($string)
      );
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
    
    
    $string =
    'foo' . "\r\n" .
    'bar' . "\r\n" .
    'test' . "\r\n";
    
    $this->assertEqual($string, $this->_encoder->encodeString($string));
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
    
    $input =
    'abcdefghijklmnopqrstuvwxyz' .           //26
    'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .           //52
    '1234567890' .                           //62
    'abcdefghijklmn' .                       //76 *
    'opqrstuvwxyz' .                         //12
    'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .           //38
    '1234567890' .                           //48
    'abcdefghijklmnopqrstuvwxyz' .           //74
    'AB' .                                   //76 *
    'CDEFGHIJKLMNOPQRSTUVWXYZ';              //24
    
    $output =
    'abcdefghijklmnopqrstuvwxyz' .           //26
    'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .           //52
    '1234567890' .                           //62
    'abcdefghijklm' . "=\r\n" .              //76 *
    'nopqrstuvwxyz' .                        //13
    'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .           //39
    '1234567890' .                           //49
    'abcdefghijklmnopqrstuvwxyz' . "=\r\n" . //76 *
    'A' .                                    //1
    'BCDEFGHIJKLMNOPQRSTUVWXYZ';             //26
    
    $this->assertEqual($output, $this->_encoder->encodeString($input));
  }
  
}
