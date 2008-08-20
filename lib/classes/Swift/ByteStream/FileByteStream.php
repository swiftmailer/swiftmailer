<?php

/*
 Bi-Directional FileStream in Swift Mailer.
 
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

//@require 'Swift/ByteStream/AbstractFilterableInputStream.php';
//@require 'Swift/InputByteStream.php';
//@require 'Swift/FileStream.php';
//@require 'Swift/IoException.php';

/**
 * Allows reading and writing of bytes to and from a file.
 * @package Swift
 * @subpackage ByteStream
 * @author Chris Corbyn
 */
class Swift_ByteStream_FileByteStream
  extends Swift_ByteStream_AbstractFilterableInputStream
  implements Swift_FileStream
{
  
  /** The internal pointer offset */
  private $_offset = 0;
  
  /** The path to the file */
  private $_path;
  
  /** The mode this file is opened in for writing */
  private $_mode;
  
  /** A lazy-loaded resource handle for reading the file */
  private $_reader;
  
  /** A lazy-loaded resource handle for writing the file */
  private $_writer;
  
  /** If magic_quotes_runtime is on, this will be true */
  private $_quotes = false;
  
  /**
   * Create a new FileByteStream for $path.
   * @param string $path
   * @param string $writable if true
   */
  public function __construct($path, $writable = false)
  {
    $this->_path = $path;
    $this->_mode = $writable ? 'w+b' : 'rb';
    $this->_quotes = get_magic_quotes_runtime();
  }
  
  /**
   * Get the complete path to the file.
   * @return string
   */
  public function getPath()
  {
    return $this->_path;
  }
  
  /**
   * Reads $length bytes from the stream into a string and moves the pointer
   * through the stream by $length. If less bytes exist than are requested the
   * remaining bytes are given instead. If no bytes are remaining at all, boolean
   * false is returned.
   * @param int $length
   * @return string
   * @throws Swift_IoException
   */
  public function read($length)
  {
    $fp = $this->_getReadHandle();
    if (!feof($fp))
    {
      if ($this->_quotes)
      {
        set_magic_quotes_runtime(0);
      }
      $bytes = fread($fp, $length);
      if ($this->_quotes)
      {
        set_magic_quotes_runtime(1);
      }
      $this->_offset = ftell($fp);
      return $bytes;
    }
    else
    {
      return false;
    }
  }
  
  /**
   * Move the internal read pointer to $byteOffset in the stream.
   * @param int $byteOffset
   * @return boolean
   */
  public function setReadPointer($byteOffset)
  {
    if (isset($this->_reader))
    {
      fseek($this->_reader, $byteOffset, SEEK_SET);
    }
    $this->_offset = $byteOffset;
  }
  
  // -- Private methods
  
  /** Just write the bytes to the file */
  protected function _commit($bytes)
  {
    fwrite($this->_getWriteHandle(), $bytes);
    $this->_resetReadHandle();
  }
  
  /** Not used */
  protected function _flush()
  {
  }
  
  /** Get the resource for reading */
  private function _getReadHandle()
  {
    if (!isset($this->_reader))
    {
      if (!$this->_reader = fopen($this->_path, 'rb'))
      {
        throw new Swift_IoException(
          'Unable to open file for reading [' . $this->_path . ']'
          );
      }
      fseek($this->_reader, $this->_offset, SEEK_SET);
    }
    return $this->_reader;
  }
  
  /** Get the resource for writing */
  private function _getWriteHandle()
  {
    if (!isset($this->_writer))
    {
      if (!$this->_writer = fopen($this->_path, $this->_mode))
      {
        throw new Swift_IoException(
          'Unable to open file for writing [' . $this->_path . ']'
          );
      }
    }
    return $this->_writer;
  }
  
  /** Force a reload of the resource for writing */
  private function _resetWriteHandle()
  {
    if (isset($this->_writer))
    {
      fclose($this->_writer);
      $this->_writer = null;
    }
  }
  
  /** Force a reload of the resource for reading */
  private function _resetReadHandle()
  {
    if (isset($this->_reader))
    {
      fclose($this->_reader);
      $this->_reader = null;
    }
  }
  
}
