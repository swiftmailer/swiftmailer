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

require_once dirname(__FILE__) . '/../ContentEncoder.php';
require_once dirname(__FILE__) . '/../FieldChangeObserver.php';
require_once dirname(__FILE__) . '/../../Encoder/QpEncoder.php';
require_once dirname(__FILE__) . '/../../ByteStream.php';
require_once dirname(__FILE__) . '/../../CharacterStream.php';

/**
 * Handles Quoted Printable (QP) Transfer Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_ContentEncoder_QpContentEncoder extends Swift_Encoder_QpEncoder
  implements Swift_Mime_ContentEncoder, Swift_Mime_FieldChangeObserver
{
  
  /**
   * Temporarily gets populated with a ByteStream during some internal writes.
   * @var Swift_ByteStream
   * @access private
   */
  private $_temporaryInputByteStream;
  
  /**
   * Creates a new QpContentEncoder for the given CharacterStream.
   * @param Swift_CharacterStream $charStream to use for reading characters
   */
  public function __construct(Swift_CharacterStream $charStream)
  {
    parent::__construct($charStream);
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
    return $this->_doEncodeString($string, $firstLineOffset, $maxLineLength, true);
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
   * Encode stream $in to stream $out.
   * QP encoded strings have a maximum line length of 76 characters.
   * If the first line needs to be shorter, indicate the difference with
   * $firstLineOffset.
   * @param Swift_ByteStream $os output stream
   * @param Swift_ByteStream $is input stream
   * @param int $firstLineOffset
   */
  public function encodeByteStream(
    Swift_ByteStream $os, Swift_ByteStream $is, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    $this->_doEncodeByteStream($os, $is, $firstLineOffset, $maxLineLength, false);
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
   * Changes the charset of the CharacterStream if changed.
   * @param string $field in lowercase ALPHA
   * @param mixed $value
   */
  public function fieldChanged($field, $value)
  {
    if ('charset' == $field)
    {
      $this->getCharacterStream()->setCharacterSet($value);
    }
  }
  
  // -- Protected methods
  
  /**
   * Internal callback method which appends bytes to the end of a ByteStream
   * held internally temporarily.
   * @param string $bytes
   * @access private
   */
  protected function _appendToTemporaryInputByteStream($bytes)
  {
    $this->_temporaryInputByteStream->write($bytes);
  }
  
  // -- Private methods
  
  /**
   * Encode a byte stream.
   * @param Swift_ByteStream $os
   * @param Swift_ByteStream $is
   * @param int $firstLineOffset
   * @param int $maxLineLength
   * @param boolean $canon, if canonicalization is needed
   * @access private
   */
  private function _doEncodeByteStream(Swift_ByteStream $os, Swift_ByteStream $is,
    $firstLineOffset = 0, $maxLineLength = 0, $canon = false)
  {
    //Set default length of 76 if no other value set
    if (0 >= $maxLineLength)
    {
      $maxLineLength = 76;
    }
    
    //Empty the CharacterStream and import the ByteStream to it
    $this->getCharacterStream()->flushContents();
    $this->getCharacterStream()->importByteStream($os);
    
    //Set the temporary byte stream to write into
    $this->_temporaryInputByteStream = $is;
    
    //Encode the CharacterStream using an append method as a callback
    $this->encodeCharacterStreamCallback($this->getCharacterStream(),
      array($this, '_appendToTemporaryInputByteStream'),
      $firstLineOffset, $maxLineLength, $canon
      );
    
    //Unset the temporary ByteStream
    $this->_temporaryInputByteStream = null;
  }
  
}
