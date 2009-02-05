<?php

/*
 Generic IoBuffer implementation from Swift Mailer.
 
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
//@require 'Swift/ReplacementFilterFactory.php';
//@require 'Swift/Transport/IoBuffer.php';
//@require 'Swift/TransportException.php';

/**
 * A generic IoBuffer implementation supporting remote sockets and local processes.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_StreamBuffer
  extends Swift_ByteStream_AbstractFilterableInputStream
  implements Swift_Transport_IoBuffer
{
  
  /** A primary socket */
  private $_stream;
  
  /** The input stream */
  private $_in;
  
  /** The output stream */
  private $_out;
  
  /** Buffer initialization parameters */
  private $_params = array();
  
  /** The ReplacementFilterFactory */
  private $_replacementFactory;
  
  /** Translations performed on data being streamed into the buffer */
  private $_translations = array();
  
  /**
   * Create a new StreamBuffer using $replacementFactory for transformations.
   * @param Swift_ReplacementFilterFactory $replacementFactory
   */
  public function __construct(
    Swift_ReplacementFilterFactory $replacementFactory)
  {
    $this->_replacementFactory = $replacementFactory;
  }
  
  /**
   * Perform any initialization needed, using the given $params.
   * Parameters will vary depending upon the type of IoBuffer used.
   * @param array $params
   */
  public function initialize(array $params)
  {
    $this->_params = $params;
    switch ($params['type'])
    {
      case self::TYPE_PROCESS:
        $this->_establishProcessConnection();
        break;
      case self::TYPE_SOCKET:
      default:
        $this->_establishSocketConnection();
        break;
    }
  }
  
  /**
   * Set an individual param on the buffer (e.g. switching to SSL).
   * @param string $param
   * @param mixed $value
   */
  public function setParam($param, $value)
  {
    if (isset($this->_stream))
    {
      switch ($param)
      {
        case 'protocol':
          if (!array_key_exists('protocol', $this->_params)
            || $value != $this->_params['protocol'])
          {
            if ('tls' == $value)
            {
              stream_socket_enable_crypto(
                $this->_stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT
                );
            }
          }
          break;
      }
    }
    $this->_params[$param] = $value;
  }
  
  /**
   * Perform any shutdown logic needed.
   */
  public function terminate()
  {
    if (isset($this->_stream))
    {
      switch ($this->_params['type'])
      {
        case self::TYPE_PROCESS:
          fclose($this->_in);
          fclose($this->_out);
          proc_close($this->_stream);
          break;
        case self::TYPE_SOCKET:
        default:
          fclose($this->_stream);
          break;
      }
    }
    $this->_stream = null;
    $this->_out = null;
    $this->_in = null;
  }
  
  /**
   * Set an array of string replacements which should be made on data written
   * to the buffer.  This could replace LF with CRLF for example.
   * @param string[] $replacements
   */
  public function setWriteTranslations(array $replacements)
  {
    foreach ($this->_translations as $search => $replace)
    {
      if (!isset($replacements[$search]))
      {
        $this->removeFilter($search);
        unset($this->_translations[$search]);
      }
    }
    
    foreach ($replacements as $search => $replace)
    {
      if (!isset($this->_translations[$search]))
      {
        $this->addFilter(
          $this->_replacementFactory->createFilter($search, $replace), $search
          );
        $this->_translations[$search] = true;
      }
    }
  }
  
  /**
   * Get a line of output (including any CRLF).
   * The $sequence number comes from any writes and may or may not be used
   * depending upon the implementation.
   * @param int $sequence of last write to scan from
   * @return string
   */
  public function readLine($sequence)
  {
    if (isset($this->_out) && !feof($this->_out))
    {
      $line = fgets($this->_out);
      return $line;
    }
  }
  
  /**
   * Reads $length bytes from the stream into a string and moves the pointer
   * through the stream by $length. If less bytes exist than are requested the
   * remaining bytes are given instead. If no bytes are remaining at all, boolean
   * false is returned.
   * @param int $length
   * @return string
   */
  public function read($length)
  {
    if (isset($this->_out) && !feof($this->_out))
    {
      $ret = fread($this->_out, $length);
      return $ret;
    }
  }
  
  /** Not implemented */
  public function setReadPointer($byteOffset)
  {
  }
  
  // -- Protected methods
  
  /** Flush the stream contents */
  protected function _flush()
  {
    if (isset($this->_in))
    {
      fflush($this->_in);
    }
  }
  
  /** Write this bytes to the stream */
  protected function _commit($bytes)
  {
    if (isset($this->_in)
      && fwrite($this->_in, $bytes))
    {
      return ++$this->_sequence;
    }
  }
  
  // -- Private methods
  
  /**
   * Establishes a connection to a remote server.
   * @access private
   */
  private function _establishSocketConnection()
  {
    $host = $this->_params['host'];
    if (!empty($this->_params['protocol']))
    {
      $host = $this->_params['protocol'] . '://' . $host;
    }
    $timeout = 15;
    if (!empty($this->_params['timeout']))
    {
      $timeout = $this->_params['timeout'];
    }
    if (!$this->_stream = fsockopen($host, $this->_params['port'], $errno, $errstr, $timeout))
    {
      throw new Swift_TransportException(
        'Connection could not be established with host ' . $this->_params['host'] .
        ' [' . $errstr . ' #' . $errno . ']'
        );
    }
    if (!empty($this->_params['blocking']))
    {
      stream_set_blocking($this->_stream, 1);
    }
    else
    {
      stream_set_blocking($this->_stream, 0);
    }
    $this->_in =& $this->_stream;
    $this->_out =& $this->_stream;
  }
  
  /**
   * Opens a process for input/output.
   * @access private
   */
  private function _establishProcessConnection()
  {
    $command = $this->_params['command'];
    $descriptorSpec = array(
      0 => array('pipe', 'r'),
      1 => array('pipe', 'w'),
      2 => array('pipe', 'w')
      );
    $this->_stream = proc_open($command, $descriptorSpec, $pipes);
    stream_set_blocking($pipes[2], 0);
    if ($err = stream_get_contents($pipes[2]))
    {
      throw new Swift_TransportException(
        'Process could not be started [' . $err . ']'
        );
    }
    $this->_in =& $pipes[0];
    $this->_out =& $pipes[1];
  }
  
}
