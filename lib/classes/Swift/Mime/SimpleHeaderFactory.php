<?php

/*
 Factory for creating MIME Headers in Swift Mailer.
 
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

//@require 'Swift/Mime/HeaderFactory.php';
//@require 'Swift/Mime/HeaderEncoder.php';
//@require 'Swift/Encoder.php';
//@require 'Swift/Mime/Headers/MailboxHeader.php';
//@require 'Swift/Mime/Headers/DateHeader.php';
//@require 'Swift/Mime/Headers/UnstructuredHeader.php';
//@require 'Swift/Mime/Headers/ParameterizedHeader.php';
//@require 'Swift/Mime/Headers/IdentificationHeader.php';
//@require 'Swift/Mime/Headers/PathHeader.php';

/**
 * Creates MIME headers.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_SimpleHeaderFactory implements Swift_Mime_HeaderFactory
{

  /** The HeaderEncoder used by these headers */
  private $_encoder;
  
  /** The Encoder used by parameters */
  private $_paramEncoder;
  
  /**
   * Creates a new SimpleHeaderFactory using $encoder and $paramEncoder.
   * @param Swift_Mime_HeaderEncoder $encoder
   * @param Swift_Encoder $paramEncoder
   */
  public function __construct(Swift_Mime_HeaderEncoder $encoder,
    Swift_Encoder $paramEncoder)
  {
    $this->_encoder = $encoder;
    $this->_paramEncoder = $paramEncoder;
  }
  
  /**
   * Create a new Mailbox Header with a list of $addresses.
   * @param string $name
   * @param array|string $addresses
   * @return Swift_Mime_Header
   */
  public function createMailboxHeader($name, $addresses = null)
  {
    $header = new Swift_Mime_Headers_MailboxHeader($name, $this->_encoder);
    if (isset($addresses))
    {
      $header->setFieldBodyModel($addresses);
    }
    return $header;
  }
  
  /**
   * Create a new Date header using $timestamp (UNIX time).
   * @param string $name
   * @param int $timestamp
   * @return Swift_Mime_Header
   */
  public function createDateHeader($name, $timestamp = null)
  {
    $header = new Swift_Mime_Headers_DateHeader($name);
    if (isset($timestamp))
    {
      $header->setFieldBodyModel($timestamp);
    }
    return $header;
  }
  
  /**
   * Create a new basic text header with $name and $value.
   * @param string $name
   * @param string $value
   * @return Swift_Mime_Header
   */
  public function createTextHeader($name, $value = null)
  {
    $header = new Swift_Mime_Headers_UnstructuredHeader($name, $this->_encoder);
    if (isset($value))
    {
      $header->setFieldBodyModel($value);
    }
    return $header;
  }
  
  /**
   * Create a new ParameterizedHeader with $name, $value and $params.
   * @param string $name
   * @param string $value
   * @param array $params
   * @return Swift_Mime_ParameterizedHeader
   */
  public function createParameterizedHeader($name, $value = null,
    $params = array())
  {
    $header = new Swift_Mime_Headers_ParameterizedHeader($name,
      $this->_encoder, $this->_paramEncoder
      );
    if (isset($value))
    {
      $header->setFieldBodyModel($value);
    }
    foreach ($params as $k => $v)
    {
      $header->setParameter($k, $v);
    }
    return $header;
  }
  
  /**
   * Create a new ID header for Message-ID or Content-ID.
   * @param string $name
   * @param string|array $ids
   * @return Swift_Mime_Header
   */
  public function createIdHeader($name, $ids = null)
  {
    $header = new Swift_Mime_Headers_IdentificationHeader($name);
    if (isset($ids))
    {
      $header->setFieldBodyModel($ids);
    }
    return $header;
  }
  
  /**
   * Create a new Path header with an address (path) in it.
   * @param string $name
   * @param string $path
   * @return Swift_Mime_Header
   */
  public function createPathHeader($name, $path = null)
  {
    $header = new Swift_Mime_Headers_PathHeader($name);
    if (isset($path))
    {
      $header->setFieldBodyModel($path);
    }
    return $header;
  }
  
}
