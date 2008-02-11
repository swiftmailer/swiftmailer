<?php

/*
 A plain text transfer encoder in Swift Mailer.
 
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


require_once dirname(__FILE__) . '/../ContentEncoder.php';
require_once dirname(__FILE__) . '/../../ByteStream.php';

/**
 * Handles 7/8-bit Transfer Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_ContentEncoder_PlainContentEncoder
  implements Swift_Mime_ContentEncoder
{
  
  /**
   * The name of this encoding scheme (probably 7bit or 8bit).
   * @var string
   * @access private
   */
  private $_name;
  
  /**
   * Creates a new PlainContentEncoder with $name (probably 7bit or 8bit).
   * @param string $name
   */
  public function __construct($name)
  {
    $this->_name = $name;
  }
  
  /**
   * Used for encoding text input and ensuring the output is in the canonical
   * form (i.e. all line endings are CRLF).
   * @param string $string
   * @param int $firstLineOffset if the first line needs shortening
   * @param int $maxLineLength
   * @return string
   */
  public function canonicEncodeString($string, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    $string = $this->_canonicalize($string);
    return $this->encodeString($string, $firstLineOffset, $maxLineLength);
  }
  
  /**
   * Encode $in to $out, converting all line endings to CRLF.
   * @param Swift_ByteStream $os to read from
   * @param Swift_ByteStream $is to write to
   * @param int $firstLineOffset
   * @param int $maxLineLength - 0 indicates the default length for this encoding
   */
  public function canonicEncodeByteStream(
    Swift_ByteStream $os, Swift_ByteStream $is, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    $this->_doEncodeByteStream($os, $is, $firstLineOffset, $maxLineLength, true);
  }
  
  /**
   * Encode a given string to produce an encoded string.
   * @param string $string
   * @param int $firstLineOffset if first line needs to be shorter
   * @param int $maxLineLength - 0 indicates the default length for this encoding
   * @return string
   */
  public function encodeString($string, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    return $this->_safeWordWrap($string, $maxLineLength, "\r\n");
  }
  
  /**
   * Encode stream $in to stream $out.
   * @param Swift_ByteStream $in
   * @param Swift_ByteStream $out
   * @param int $firstLineOffset
   * @param int $maxLineLength, optional, 0 indicates the default of 78 bytes
   */
  public function encodeByteStream(
    Swift_ByteStream $os, Swift_ByteStream $is, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    $this->_doEncodeByteStream($os, $is, $firstLineOffset, $maxLineLength, false);
  }
  
  /**
   * Get the name of this encoding scheme.
   * @return string
   */
  public function getName()
  {
    return $this->_name;
  }
  
  // -- Private methods
  
  /**
   * A safer (but weaker) wordwrap for unicode.
   * @param string $string
   * @param int $length
   * @param string $le
   * @return string
   * @access private
   */
  private function _safeWordwrap($string, $length = 75, $le = "\r\n")
  {
    if (0 >= $length)
    {
      return $string;
    }
    
    $originalLines = explode($le, $string);
    
    $lines = array();
    $lineCount = 0;
    
    foreach ($originalLines as $originalLine)
    {
      $lines[] = '';
      $currentLine =& $lines[$lineCount++];
      
      $chunks = preg_split('/(?<=[\ \t,\.!\?\-&\+\/])/', $originalLine);
      
      foreach ($chunks as $chunk)
      {
        if (0 != strlen($currentLine)
          && strlen($currentLine . $chunk) > $length)
        {
          $lines[] = '';
          $currentLine =& $lines[$lineCount++];
        }
        $currentLine .= $chunk;
      }
    }
    
    return implode("\r\n", $lines);
  }
  
  /**
   * Encode a byte stream.
   * @param Swift_ByteStream $os
   * @param Swift_ByteStream $is
   * @param int $firstLineOffset
   * @param int $maxLineLength
   * @param boolean $canon, if canonicalization is needed
   */
  private function _doEncodeByteStream(
    Swift_ByteStream $os, Swift_ByteStream $is, $firstLineOffset = 0,
    $maxLineLength = 0, $canon = false)
  {
    $leftOver = '';
    $needLfStart = false;
    while (false !== $bytes = $os->read(8192))
    {
      if ($canon)
      {
        $prepend = null;
        $append = null;
        if ($needLfStart)
        {
          $prepend = "\n";
          if ("\n" == substr($bytes, 0, 1))
          {
            $bytes = substr($bytes, 1);
          }
        }
        if ("\r" == substr($bytes, -1))
        {
          $append = "\r";
          $bytes = substr($bytes, 0, -1);
          $needLfStart = true;
        }
        else
        {
          $needLfStart = false;
        }
        $bytes = $prepend . $this->_canonicalize($bytes) . $append;
      }
      
      $wrapped = $this->_safeWordWrap($leftOver . $bytes, $maxLineLength, "\r\n");
      $wrapped = substr($wrapped, strlen($leftOver)); //remove the stuff left over
      $lines = explode("\r\n", $wrapped);
      $lastLine = array_pop($lines);
      if (count($lines) == 0) //after pop
      {
        $leftOver .= $lastLine;
      }
      elseif (strlen($lastLine) < $maxLineLength)
      {
        $leftOver = $lastLine;
      }
      else
      {
        $leftOver = '';
      }
      $lines[] = $lastLine;
      $is->write(implode("\r\n", $lines));
    }
  }
  
  private function _canonicalize($string)
  {
    $string = str_replace(array("\r\n", "\r"), "\n", $string);
    return str_replace("\n", "\r\n", $string);
  }
  
}
