<?php

/*
 CharacterStream implementation using an array in Swift Mailer.

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

//@require 'Swift/CharacterStream.php';
//@require 'Swift/OutputByteStream.php';


/**
 * A CharacterStream implementation which stores characters in an internal array.
 * @package Swift
 * @subpackage CharacterStream
 * @author Chris Corbyn
 * @author Xavier De Cock <xdecock@gmail.com>
 */
class Swift_CharacterStream_ArrayCharacterStream
  implements Swift_CharacterStream
{

  /**
   * The char reader (lazy-loaded) for the current charset.
   * @var Swift_CharacterReader
   * @access private
   */
  private $_charReader;

  /**
   * A factory for creatiing CharacterReader instances.
   * @var Swift_CharacterReaderFactory
   * @access private
   */
  private $_charReaderFactory;

  /**
   * The character set this stream is using.
   * @var string
   * @access private
   */
  private $_charset;

  /**
   * Array of characters.
   * @var string[]
   * @access private
   */
  private $_array = array();

  /**
   * Size of the array of character
   * @var int
   * @access private
   */
  private $_array_size = array();

  /**
   * The current character offset in the stream.
   * @var int
   * @access private
   */
  private $_offset = 0;
  
  /**
   * Mixed Datas to enqueue
   * @var mixed
   */
  private $_toAppend = null;
  
  /**
   * May Be used if it's a string
   *
   * @var int
   */
  private $_toAppendSize = 0;
  
  /**
   * May Be used if it's a string
   *
   * @var int
   */
  private $_toAppendPos = 0;

  /**
   * Create a new CharacterStream with the given $chars, if set.
   * @param Swift_CharacterReaderFactory $factory for loading validators
   * @param string $charset used in the stream
   */
  public function __construct(Swift_CharacterReaderFactory $factory,
    $charset)
  {
    $this->setCharacterReaderFactory($factory);
    $this->setCharacterSet($charset);
  }

  /* -- Changing parameters of the stream -- */

  /**
   * Set the character set used in this CharacterStream.
   * @param string $charset
   */
  public function setCharacterSet($charset)
  {
    $this->_charset = $charset;
    $this->_charReader = null;
  }

  /**
   * Set the CharacterReaderFactory for multi charset support.
   * @param Swift_CharacterReaderFactory $factory
   */
  public function setCharacterReaderFactory(
    Swift_CharacterReaderFactory $factory)
  {
    $this->_charReaderFactory = $factory;
  }

  /* -- Pull datas from the stream -- */
  
  /**
   * Read $length characters from the stream and move the internal pointer
   * $length further into the stream.
   * @param int $length
   * @return string
   */
  public function read($length)
  {
    if ($this->_offset == $this->_array_size)
    {
      return false;
    }

    // Don't use array slice
    $arrays = array();
    $end = $length + $this->_offset;
    for ($i = $this->_offset; $i < $end; ++$i)
    {
      if (!isset($this->_array[$i]))
      {
        break;
      }
      $arrays[] = $this->_array[$i];
    }
    $this->_offset += $i - $this->_offset; // Limit function calls
    $chars = false;
    foreach ($arrays as $array)
    {
      $chars .= implode('', array_map('chr', $array));
    }
    return $chars;
  }

  /**
   * Read $length characters from the stream and return a 1-dimensional array
   * containing there octet values.
   * @param int $length
   * @return int[]
   */
  public function readBytes($length)
  {
    if ($this->_offset == $this->_array_size)
    {
      return false;
    }
    $arrays = array();
    $end = $length + $this->_offset;
    for ($i = $this->_offset; $i < $end; ++$i)
    {
      if (!isset($this->_array[$i]))
      {
        break;
      }
      $arrays[] = $this->_array[$i];
    }
    $this->_offset += ($i - $this->_offset); // Limit function calls
    return call_user_func_array('array_merge', $arrays);
  }

  /* -- Alters Stream Datas -- */
  
  /**
   * Overwrite this character stream using the byte sequence in the byte stream.
   * @param Swift_OutputByteStream $os output stream to read from
   */
  public function importByteStream(Swift_OutputByteStream $os)
  {
    $this->flushContents();
    $this->_appendByteStream($os);
  }

  /**
   * Import a string a bytes into this CharacterStream, overwriting any existing
   * data in the stream.
   * @param string $string
   */
  public function importString($string)
  {
    $this->flushContents();
    $this->write($string);
  }
  
  /**
   * Write $chars to the end of the stream.
   * @param string $chars
   */
  public function write($chars)
  {
    $this->_appendString($chars);
  }

  /**
   * Move the internal pointer to $charOffset in the stream.
   * @param int $charOffset
   */
  public function setPointer($charOffset)
  {
    if ($charOffset > $this->_array_size)
    {
      $charOffset = $this->_array_size;
    }
    elseif ($charOffset < 0)
    {
      $charOffset = 0;
    }
    $this->_offset = $charOffset;
  }

  /**
   * Empty the stream and reset the internal pointer.
   */
  public function flushContents()
  {
    $this->_offset = 0;
    $this->_array = array();
    $this->_array_size = 0;
  }

  /* -- Helpers Function -- */
  /**
   * Enqueue a string to be appended
   *
   * @param string $string
   */
  private function _appendString($string)
  {
  	$this->_toAppend = $string;
  	$this->_toAppendSize = strlen($string);
  	$this->_toAppendPos = 0;
  	$this->_doAppend();
  }
  
  /**
   * Enqueue an outputByteStream to be appended
   *
   * @param Swift_OutputByteStream $os
   */
  private function _appendByteStream(Swift_OutputByteStream $os)
  {
  	$this->_toAppend = $os;
  	$os->setReadPointer(0);
  	$this->_doAppend();
  }
  
  /**
   * Append the queued datas to the content
   *
   */
  private function _doAppend()
  {
  	if (!isset($this->_charReader))
    {
      $this->_charReader = $this->_charReaderFactory->getReaderFor(
        $this->_charset);
    }
    /* Init work */
  	$workWithString = is_string($this->_toAppend);
  	$startLength = $this->_charReader->getInitialByteSize();
  	
  	/* Buffer Work */
  	$buffer = array(0);
  	$bufferPos = 1;
  	$bufferLen = 1;
  	$bufferRemLen = 0;
  	$hasDatas = 1;
    do
    {
      $bytes = array();
      // Buffer Filing
      if ($bufferRemLen < $startLength)
      {
        $new = $this->_reloadBuffer(512, $workWithString);
        if ($new)
        {
          $oldBuf = $buffer;
          $buffer = array();
          for ($i = $bufferPos; $i < $bufferLen; ++$i)
          {
          	$buffer[] = $oldBuf[$i];
          }
          foreach ($new as $b)
          {
          	$buffer[] = $b;
          }
          $bufferPos=0;
          $bufferRemLen=$bufferLen=count($buffer);
        }
        else
        {
          $hasDatas = false;
        }
      }
      if ($bufferRemLen > 0)
      {
        $size = 0;
        for ($i = 0; $i < $startLength && isset($buffer[$bufferPos]); ++$i)
        {
          ++$size;
          --$bufferRemLen;
          $bytes[] = $buffer[$bufferPos++];
        }
        $need = $this->_charReader->validateByteSequence(
          $bytes, $size);
        if ($need > 0)
        {
          if ($bufferRemLen < $need)
          {
            $new = $this->_reloadBuffer($need, $workWithString);
            if ($new)
            {
              $buffer = array_merge($buffer, $new);
              $bufferLen = count($buffer);
              $bufferRemLen =  $bufferLen - $bufferPos;
            }
          }
          for ($i = 0; $i < $need && isset($buffer[$bufferPos]); ++$i)
          {
          	--$bufferRemLen;
            $bytes[] = $buffer[$bufferPos++];
          }
        }
        $this->_array[] = $bytes;
        ++$this->_array_size;
      }
    }
    while ($hasDatas);
  }
  
  /**
   * Helper to load datas in the buffer
   *
   * @param ressource $fp
   * @param int $len
   * @return array [int]
   */
  private function _reloadBuffer($len, $isString)
  {
  	if ($isString)
  	{ /* string code */
      if ($this->_toAppendSize > $this->_toAppendPos)
      {
      	$sub=substr($this->_toAppend, $this->_toAppendPos, $len);
      	$this->_toAppendPos+=$len;
      	return unpack('C*',$sub);
      }
      return false;
  	}
  	else
  	{ /* OutputByteStream Code */
  	  return unpack('C*',$this->_toAppend->read($len));
  	}
    return false;
  }
}
