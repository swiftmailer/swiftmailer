<?php

/*
 The Quoted Printable transfer encoder in Swift Mailer.
 
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

//@require 'Swift/Mime/ContentEncoder.php';
//@require 'Swift/Encoder/QpEncoder.php';
//@require 'Swift/InputByteStrean.php';
//@require 'Swift/OutputByteStream.php';
//@require 'Swift/CharacterStream.php';

/**
 * Handles Quoted Printable (QP) Transfer Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_ContentEncoder_QpContentEncoder extends Swift_Encoder_QpEncoder
  implements Swift_Mime_ContentEncoder
{
  
  /**
   * Creates a new QpContentEncoder for the given CharacterStream.
   * @param Swift_CharacterStream $charStream to use for reading characters
   * @param boolean $canonical true if canonicalization should occur
   */
  public function __construct(Swift_CharacterStream $charStream, $canonical = false)
  {
    parent::__construct($charStream, $canonical);
  }
  
  /**
   * Encode stream $in to stream $out.
   * QP encoded strings have a maximum line length of 76 characters.
   * If the first line needs to be shorter, indicate the difference with
   * $firstLineOffset.
   * @param Swift_OutputByteStream $os output stream
   * @param Swift_InputByteStream $is input stream
   * @param int $firstLineOffset
   * @param int $maxLineLength
   */
  public function encodeByteStream(
    Swift_OutputByteStream $os, Swift_InputByteStream $is, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    if ($maxLineLength > 76 || $maxLineLength <= 0)
    {
      $maxLineLength = 76;
    }
    
    $thisLineLength = $maxLineLength - $firstLineOffset;
    
    $this->_charStream->flushContents();
    $this->_charStream->importByteStream($os);
    
    $currentLine = '';
    $prepend = '';
    
    while (false !== $bytes = $this->_nextSequence())
    {
      if ($this->_canonical)
      {
        $bytes = $this->_canonicalize($bytes);
      }
      $enc = $this->_encodeByteSequence($bytes);
      if ($currentLine && strlen($currentLine . $enc) >= $thisLineLength)
      {
        $is->write($prepend . $this->_standardize($currentLine));
        $currentLine = '';
        $prepend = "=\r\n";
        $thisLineLength = $maxLineLength;
      }
      $currentLine .= $enc;
    }
    if (strlen($currentLine))
    {
      $is->write($prepend . $this->_standardize($currentLine));
    }
  }
  
  /**
   * Get the name of this encoding scheme.
   * Returns the string 'quoted-printable'.
   * @return string
   */
  public function getName()
  {
    return 'quoted-printable';
  }
  
  /**
   * Notify this observer that the entity's charset has changed.
   * @param string $charset
   */
  public function charsetChanged($charset)
  {
    $this->_charStream->setCharacterSet($charset);
  }
  
}
