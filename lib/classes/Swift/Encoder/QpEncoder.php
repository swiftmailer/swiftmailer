<?php

/*
 The Quoted Printable encoder in Swift Mailer.
 
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
 */

//@require 'Swift/Encoder.php';
//@require 'Swift/CharacterStream.php';

/**
 * Handles Quoted Printable (QP) Encoding in Swift Mailer.
 * Possibly the most accurate RFC 2045 QP implementation found in PHP.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
class Swift_Encoder_QpEncoder implements Swift_Encoder
{
  
  /**
   * The CharacterStream which is used for reading characters (as opposed to bytes).
   * @var Swift_CharacterStream
   * @access private
   */
  private $_charStream;
  
  /**
   * Linear whitespace bytes.
   * @var int[]
   * @access private
   */
  private $_lwspBytes = array();
  
  /**
   * Linear whitespace characters.
   * @var string[]
   * @access private
   */
  private $_lwspChars = array();
  
  /**
   * CR and LF bytes.
   * @var int[]
   * @access private
   */
  private $_crlfBytes = array();
  
  /**
   * CR and LF characters.
   * @var string[]
   * @access private
   */
  private $_crlfChars = array();
  
  /**
   * Bytes to allow through the encoder without being translated.
   * @var int[]
   * @access private
   */
  private $_permittedBytes = array();
  
  /**
   * Temporarily grows as a string to be returned during some internal writes.
   * @var string
   * @access private
   */
  private $_temporaryReturnString;
  
  /**
   * Pre-computed QP for HUGE optmization.
   * @var string[]
   * @access private
   */
  private static $_qpMap = array(
    0   => '=00', 1   => '=01', 2   => '=02', 3   => '=03', 4   => '=04',
    5   => '=05', 6   => '=06', 7   => '=07', 8   => '=08', 9   => '=09',
    10  => '=0A', 11  => '=0B', 12  => '=0C', 13  => '=0D', 14  => '=0E',
    15  => '=0F', 16  => '=10', 17  => '=11', 18  => '=12', 19  => '=13',
    20  => '=14', 21  => '=15', 22  => '=16', 23  => '=17', 24  => '=18',
    25  => '=19', 26  => '=1A', 27  => '=1B', 28  => '=1C', 29  => '=1D',
    30  => '=1E', 31  => '=1F', 32  => '=20', 33  => '=21', 34  => '=22',
    35  => '=23', 36  => '=24', 37  => '=25', 38  => '=26', 39  => '=27',
    40  => '=28', 41  => '=29', 42  => '=2A', 43  => '=2B', 44  => '=2C',
    45  => '=2D', 46  => '=2E', 47  => '=2F', 48  => '=30', 49  => '=31',
    50  => '=32', 51  => '=33', 52  => '=34', 53  => '=35', 54  => '=36',
    55  => '=37', 56  => '=38', 57  => '=39', 58  => '=3A', 59  => '=3B',
    60  => '=3C', 61  => '=3D', 62  => '=3E', 63  => '=3F', 64  => '=40', 
    65  => '=41', 66  => '=42', 67  => '=43', 68  => '=44', 69  => '=45',
    70  => '=46', 71  => '=47', 72  => '=48', 73  => '=49', 74  => '=4A',
    75  => '=4B', 76  => '=4C', 77  => '=4D', 78  => '=4E', 79  => '=4F',
    80  => '=50', 81  => '=51', 82  => '=52', 83  => '=53', 84  => '=54',
    85  => '=55', 86  => '=56', 87  => '=57', 88  => '=58', 89  => '=59',
    90  => '=5A', 91  => '=5B', 92  => '=5C', 93  => '=5D', 94  => '=5E',
    95  => '=5F', 96  => '=60', 97  => '=61', 98  => '=62', 99  => '=63',
    100 => '=64', 101 => '=65', 102 => '=66', 103 => '=67', 104 => '=68',
    105 => '=69', 106 => '=6A', 107 => '=6B', 108 => '=6C', 109 => '=6D',
    110 => '=6E', 111 => '=6F', 112 => '=70', 113 => '=71', 114 => '=72',
    115 => '=73', 116 => '=74', 117 => '=75', 118 => '=76', 119 => '=77',
    120 => '=78', 121 => '=79', 122 => '=7A', 123 => '=7B', 124 => '=7C',
    125 => '=7D', 126 => '=7E', 127 => '=7F', 128 => '=80', 129 => '=81',
    130 => '=82', 131 => '=83', 132 => '=84', 133 => '=85', 134 => '=86',
    135 => '=87', 136 => '=88', 137 => '=89', 138 => '=8A', 139 => '=8B',
    140 => '=8C', 141 => '=8D', 142 => '=8E', 143 => '=8F', 144 => '=90', 
    145 => '=91', 146 => '=92', 147 => '=93', 148 => '=94', 149 => '=95',
    150 => '=96', 151 => '=97', 152 => '=98', 153 => '=99', 154 => '=9A',
    155 => '=9B', 156 => '=9C', 157 => '=9D', 158 => '=9E', 159 => '=9F',
    160 => '=A0', 161 => '=A1', 162 => '=A2', 163 => '=A3', 164 => '=A4',
    165 => '=A5', 166 => '=A6', 167 => '=A7', 168 => '=A8', 169 => '=A9',
    170 => '=AA', 171 => '=AB', 172 => '=AC', 173 => '=AD', 174 => '=AE',
    175 => '=AF', 176 => '=B0', 177 => '=B1', 178 => '=B2', 179 => '=B3',
    180 => '=B4', 181 => '=B5', 182 => '=B6', 183 => '=B7', 184 => '=B8',
    185 => '=B9', 186 => '=BA', 187 => '=BB', 188 => '=BC', 189 => '=BD',
    190 => '=BE', 191 => '=BF', 192 => '=C0', 193 => '=C1', 194 => '=C2',
    195 => '=C3', 196 => '=C4', 197 => '=C5', 198 => '=C6', 199 => '=C7',
    200 => '=C8', 201 => '=C9', 202 => '=CA', 203 => '=CB', 204 => '=CC',
    205 => '=CD', 206 => '=CE', 207 => '=CF', 208 => '=D0', 209 => '=D1',
    210 => '=D2', 211 => '=D3', 212 => '=D4', 213 => '=D5', 214 => '=D6',
    215 => '=D7', 216 => '=D8', 217 => '=D9', 218 => '=DA', 219 => '=DB',
    220 => '=DC', 221 => '=DD', 222 => '=DE', 223 => '=DF', 224 => '=E0', 
    225 => '=E1', 226 => '=E2', 227 => '=E3', 228 => '=E4', 229 => '=E5',
    230 => '=E6', 231 => '=E7', 232 => '=E8', 233 => '=E9', 234 => '=EA',
    235 => '=EB', 236 => '=EC', 237 => '=ED', 238 => '=EE', 239 => '=EF',
    240 => '=F0', 241 => '=F1', 242 => '=F2', 243 => '=F3', 244 => '=F4',
    245 => '=F5', 246 => '=F6', 247 => '=F7', 248 => '=F8', 249 => '=F9',
    250 => '=FA', 251 => '=FB', 252 => '=FC', 253 => '=FD', 254 => '=FE', 
    255 => '=FF'
    );
  
  /**
   * Creates a new QpEncoder for the given CharacterStream.
   * @param Swift_CharacterStream $charStream to use for reading characters
   */
  public function __construct(Swift_CharacterStream $charStream)
  {
    $this->_charStream = $charStream;
    
    $this->_crlfBytes = array('CR' => 0x0D, 'LF' => 0x0A);
    $this->_crlfChars = array(
      'CR' => "\r", 'LF' => "\n"
      );
    
    $this->_lwspBytes = array('HT' => 0x09, 'SPACE' => 0x20);
    $this->_lwspChars = array(
      'HT' => "\t",
      'SPACE' => ' '
      );
    
    $this->_permittedBytes = array_merge(
      $this->_lwspBytes, range(0x21, 0x3C), range(0x3E, 0x7E)
      );
  }
  
  /**
   * Takes an unencoded string and produces a QP encoded string from it.
   * QP encoded strings have a maximum line length of 76 characters.
   * If the first line needs to be shorter, indicate the difference with
   * $firstLineOffset.
   * @param string $string to encode
   * @param int $firstLineOffset, optional
   * @param int $maxLineLength, optional, 0 indicates the default of 76 chars
   * @return string
   */
  public function encodeString($string, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    return $this->_doEncodeString($string, $firstLineOffset,
      $maxLineLength, false);
  }
  
  /**
   * Get the CharacterStream used in encoding.
   * @return Swift_CharacterStream
   * @access protected
   */
  protected function getCharacterStream()
  {
    return $this->_charStream;
  }
  
  /**
   * Internal method which does the bulk of the work, repeatedly invoking an
   * internal callback method to append bytes to the output.
   * @param Swift_CharacterStream $charStream to read from
   * @param callback $callback for appending
   * @param int $firstlineOffset
   * @param int $maxLineLength
   * @param boolean $canon, if canonicalization is needed
   * @access protected
   */
  protected function encodeCharacterStreamCallback(
    Swift_CharacterStream $charStream, $callback, $firstLineOffset,
    $maxLineLength, $canon = false)
  {
    //Variables used for tracking
    $nextChar = null; $bufNext = null; $deferredLwspChar = null;
    $expectedLfChar = false; $needLf = false;
    $lineLength = 0; $lineCount = 0;
    
    do
    {
      //Zero the firstLineOffset if no longer on first line
      $firstLineOffset = $lineCount > 0 ? 0 : $firstLineOffset;
      
      //If just starting, read from stream, else use $nextChar from last loop
      if (false === $thisChar = is_null($nextChar) ?
        $charStream->read(1) : $nextChar)
      {
        break;
      }
      
      //Always have knowledge of at least two chars at a time
      $nextChar = is_null($bufNext) ? $charStream->read(1) : $bufNext;
      $bufNext = null;
      
      //Canonicalize
      if ($canon)
      {
        if ($this->_crlfChars['CR'] == $thisChar)
        {
          $needLf = true;
        }
        elseif ($this->_crlfChars['LF'] == $thisChar && !$needLf)
        {
          $needLf = true;
          $bufNext = $nextChar;
          $thisChar = $this->_crlfChars['CR'];
          $nextChar = $this->_crlfChars['LF'];
        }
        else
        {
          $needLf = false;
        }
        
        if ($needLf)
        {
          if ($this->_crlfChars['LF'] != $nextChar)
          {
            $bufNext = $nextChar;
            $nextChar = $this->_crlfChars['LF'];
          }
        }
      }
      $thisCharEncoded = $this->_encodeCharacter($thisChar);
      
      //Adjust max line length if needed
      if (false !== $nextChar)
      {
        $thisMaxLineLength = $maxLineLength - $firstLineOffset - 1;
      }
      else
      {
        $thisMaxLineLength = $maxLineLength - $firstLineOffset;
      }
      
      //Currently looking at LWSP followed by CR
      if (in_array(ord($thisCharEncoded), $this->_lwspBytes)
        && $this->_crlfChars['CR'] == $nextChar)
      {
        $deferredLwspChar = $thisCharEncoded;
      }
      //Looking at LWSP at end of string
      elseif (in_array(ord($thisCharEncoded), $this->_lwspBytes)
        && false === $nextChar)
      {
        $this->_writeSequenceToCallback(self::$_qpMap[ord($thisCharEncoded)],
            $callback, $thisMaxLineLength, $lineLength, $lineCount
            );
      }
      //Currently looking at CRLF
      elseif ($this->_crlfChars['CR'] == $thisChar
        && $this->_crlfChars['LF'] == $nextChar)
      {
        //If a LWSP char was deferred due to the CR
        if (!is_null($deferredLwspChar))
        {
          $this->_writeSequenceToCallback(self::$_qpMap[ord($deferredLwspChar)],
            $callback, $thisMaxLineLength, $lineLength, $lineCount
            );
          $deferredLwspChar = null;
        }
        
        $this->_writeSequenceToCallback($thisChar, $callback, $thisMaxLineLength,
          $lineLength, $lineCount
          );
        $expectedLfChar = true;
      }
      //Currently looking at an expected LF (following a CR)
      elseif ($this->_crlfChars['LF'] == $thisChar && $expectedLfChar)
      {
        $this->_writeSequenceToCallback($thisChar, $callback, $thisMaxLineLength,
          $lineLength, $lineCount
          );
        $expectedLfChar = false;
      }
      //Nothing special about this character, just write it
      else
      {
        //If a LWSP was deferred but not used, write it as normal
        if (!is_null($deferredLwspChar))
        {
          $this->_writeSequenceToCallback($deferredLwspChar, $callback,
            $thisMaxLineLength, $lineLength, $lineCount
            );
          $deferredLwspChar = null;
        }
        
        //Write the endoded character as normal
        $this->_writeSequenceToCallback($thisCharEncoded, $callback,
            $thisMaxLineLength, $lineLength, $lineCount
            );
      }
    }
    while(false !== $nextChar);
  }
  
  /**
   * Encode a single character (maybe multi-byte).
   * @param string $char
   * @return string
   * @access private
   */
  private function _encodeCharacter($char)
  {
    if (!is_string($char))
    {
      return false;
    }
    
    $charEncoded = '';
    
    foreach (unpack('C*', $char) as $octet)
    {
      if (!in_array($octet, $this->getPermittedBytes()))
      {
        $charEncoded .= self::$_qpMap[$octet];
      }
      else
      {
        $charEncoded .= pack('C', $octet);
      }
    }
    
    return $charEncoded;
  }
  
  /**
   * Internal method to write a sequence of bytes into a callback method.
   * @param string $sequence of bytes
   * @param callback $callback to send $sequence to
   * @param int $maxLineLength
   * @param int &$lineLength currently
   * @param int &$lineCount currently
   * @access private
   */
  private function _writeSequenceToCallback($sequence, $callback, $maxLineLength,
    &$lineLength, &$lineCount)
  {
    $sequenceLength = strlen($sequence);
    $lineLength += $sequenceLength;
    if ($maxLineLength < $lineLength)
    {
      $sequence = "=\r\n" . $sequence;
      $lineLength = $sequenceLength;
      ++$lineCount;
    }
    
    call_user_func($callback, $sequence);
  }
  
  /**
   * Internal callback method which appends bytes to the end of a string
   * held internally temporarily.
   * @param string $bytes
   * @access private
   */
  private function _appendToTemporaryReturnString($bytes)
  {
    $this->_temporaryReturnString .= $bytes;
  }
  
  // -- Points of extension
  
  /**
   * Get the byte values which are permitted in their unencoded form.
   * @return int[]
   * @access protected
   */
  protected function getPermittedBytes()
  {
    return $this->_permittedBytes;
  }
  
  protected function _doEncodeString($string, $firstLineOffset = 0,
    $maxLineLength = 0, $canon = false)
  {
    //Set default length of 76 if no other value set
    if (0 >= $maxLineLength || 76 < $maxLineLength)
    {
      $maxLineLength = 76;
    }
    
    //Empty the CharacterStream and import the string to it
    $this->_charStream->flushContents();
    $this->_charStream->importString($string);
    
    //Set the temporary string to write into
    $this->_temporaryReturnString = '';
    
    //Encode the CharacterStream using an append method as a callback
    $this->encodeCharacterStreamCallback(
      $this->_charStream, array($this, '_appendToTemporaryReturnString'),
      $firstLineOffset, $maxLineLength, $canon
      );
    
    //Copy the temporary return value
    $ret = $this->_temporaryReturnString;
    
    //Unset the temporary return value
    $this->_temporaryReturnString = null;
    
    //Return string with data appended via callback
    return $ret;
  }
  
}
