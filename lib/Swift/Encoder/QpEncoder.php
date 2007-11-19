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


require_once dirname(__FILE__) . '/../Encoder.php';

/**
 * Handles Quoted Printable (QP) Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
class Swift_Encoder_QpEncoder implements Swift_Encoder
{
  
  /**
   * The character set being encoded.
   * @var string
   */
  private $_charset;
  
  /**
   * True if the multibyte encoding library is present.
   * @var boolean
   * @access private
   */
  private $_hasMb = false;
  
  /**
   * Linear whitespace bytes.
   * @var int[]
   */
  private $_lwsp = array();
  
  /**
   * Bytes to allow through the encoder without being translated.
   * @var int[]
   */
  private $_permittedBytes = array();
  
  /**
   * Creates a new QpEncoder for the given charset.
   * @param string $charset
   */
  public function __construct($charset = null)
  {
    $this->_charset = $charset;
    $this->_hasMb = (
      function_exists('mb_subtr')
      && function_exists('mb_strlen')
      && function_exists('mb_internal_encoding')
      );
    $this->_lwsp = array(0x09, 0x20);
    $this->_permittedBytes = array_merge(
      $this->_lwsp, range(0x21, 0x3C), range(0x3E, 0x7E)
      );
  }
  
  /**
   * Takes an unencoded string and produce a QP encoded string from it.
   * QP encoded strings have a maximum line length of 76 *characters*.
   * If the first line needs to be shorter, indicate the difference with
   * $firstLineOffset.
   *
   * @param string $string to encode
   * @param int $firstLineOffset
   * @return string
   */
  public function encodeString($string, $firstLineOffset = 0)
  {
    //RFC 2045, 6.7 (4)
    $lines = explode("\r\n", $string);
    
    foreach ($lines as $i => $line)
    {
      $lineEncoded = '';
      $wrappedLines = array();
      
      while (0 != strlen($line))
      {
        $char = $this->_substr($line, 0, 1, $this->_charset);
        $line = $this->_substr($line, 1, null, $this->_charset);
        
        //RFC 2045, 6.7 (1 & 2)
        $charEncoded = '';
        for ($bytePos = 0; $bytePos < strlen($char); $bytePos++)
        {
          $byte = $char{$bytePos};
          $octet = ord($byte);
          
          if (!in_array($octet, $this->_permittedBytes))
          {
            $charEncoded .= sprintf('=%02X', $octet);
          }
          else
          {
            $charEncoded .= $byte;
          }
        }
        
        $maxLength = 75;
        
        //RFC 2045, 6.7 (3)
        if (0 == strlen($line))
        {
          $maxLength = 76;
          
          $lastOctet = ord(substr($charEncoded, -1));
          if (in_array($lastOctet, $this->_lwsp))
          {
            $charEncodedLwsp = substr($charEncoded, 0, -1) . sprintf('=%02X', $lastOctet);
            
            //If soft break is going to occur after encoding LWSP, soft break before
            if (strlen($lineEncoded . $charEncodedLwsp) > $maxLength)
            {
              $wrappedLines[] = $lineEncoded;
              $lineEncoded = '';
            }
            else //Fix LWSP encoding
            {
              $charEncoded = $charEncodedLwsp;
            }
          }
        }
        
        //RFC 2045, 6.7 (5)
        // Leaving room for =
        if (strlen($lineEncoded . $charEncoded) > $maxLength)
        {
          $wrappedLines[] = $lineEncoded;
          $lineEncoded = '';
        }
        
        $lineEncoded .= $charEncoded;
      }
      
      if (strlen($lineEncoded) != 0)
      {
        $wrappedLines[] = $lineEncoded;
      }
      
      $lines[$i] = implode("=\r\n", $wrappedLines);
    }
    
    //RFC 2045, 6.7 (4)
    return implode("\r\n", $lines);
  }
  
  /**
   * Selective substr() which uses mb_substr() if possible.
   * @param string $string
   * @param int $start
   * @param int $length
   * @param string $encoding
   * @return string
   */
  private function _substr($string, $start, $length = null, $encoding = null)
  {
    if ($this->_hasMb)
    {
      if (is_null($length))
      {
        $length = mb_strlen($string);
      }
      
      if (is_null($encoding))
      {
        $encoding = mb_internal_encoding();
      }
      
      return mb_substr($string, $start, $length, $encoding);
    }
    else
    {
      if (is_null($length))
      {
        $length = strlen($string);
      }
      
      return substr($string, $start, $length);
    }
  }
  
}
