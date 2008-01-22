<?php

/*
 HeaderFactory Interface in Swift Mailer.
 
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

require_once dirname(__FILE__) . '/../HeaderFactory.php';
require_once dirname(__FILE__) . '/../Header.php';
require_once dirname(__FILE__) . '/../HeaderEncoder.php';
require_once dirname(__FILE__) . '/../../Encoder.php';


/**
 * A Simple Factory for making Headers.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_HeaderFactory_SimpleHeaderFactory
  implements Swift_Mime_HeaderFactory
{
  
  /**
   * The default character set of Headers.
   * @var string
   * @access private
   */
  private $_charset = 'utf-8';
  
  /**
   * The default language in Headers.
   * @var string
   * @access private
   */
  private $_lang = 'en';
  
  /**
   * The default line length of Headers.
   * @var int
   * @access private
   */
  private $_lineLength = 78;
  
  /**
   * The default encoding method in Headers.
   * @var string
   * @access private
   */
  private $_encodingMethod = 'Q';
  
  /**
   * Q and B encoders for Headers.
   * @var Swift_Mime_HeaderEncoder[]
   * @access private
   */
  private $_encoders = array('Q' => null, 'B' => null);
  
  /**
   * Attribute value encoder.
   * @var Swift_Encoder
   * @access private
   */
  private $_attrEncoder;
  
  /**
   * A map of header names to class names.
   * @var string[]
   * @access private
   */
  private $_classMap = array(
    'Return-Path' => 'Swift_Mime_Header_PathHeader',
    'Received' => 'Swift_Mime_Header_ReceivedHeader',
    'Resent-Date' => 'Swift_Mime_Header_DateHeader',
    'Resent-From' => 'Swift_Mime_Header_MailboxHeader',
    'Resent-Sender' => 'Swift_Mime_Header_MailboxHeader',
    'Resent-To' => 'Swift_Mime_Header_AddressHeader',
    'Resent-Cc' => 'Swift_Mime_Header_AddressHeader',
    'Resent-Bcc' => 'Swift_Mime_Header_AddressHeader',
    'Resent-Message-ID' => 'Swift_Mime_Header_IdentificationHeader',
    'Date' => 'Swift_Mime_Header_DateHeader',
    'From' => 'Swift_Mime_Header_MailboxHeader',
    'Sender' => 'Swift_Mime_Header_MailboxHeader',
    'Reply-To' => 'Swift_Mime_Header_AddressHeader',
    'To' => 'Swift_Mime_Header_AddressHeader',
    'Cc' => 'Swift_Mime_Header_AddressHeader',
    'Bcc' => 'Swift_Mime_Header_AddressHeader',
    'Message-ID' => 'Swift_Mime_Header_IdentificationHeader',
    'In-Reply-To' => 'Swift_Mime_Header_IdentificationHeader',
    'References' => 'Swift_Mime_Header_IdentificationHeader',
    'Subject' => 'Swift_Mime_Header_UnstructuredHeader',
    'Comments' => 'Swift_Mime_Header_UnstructuredHeader',
    'Keywords' => 'Swift_Mime_Header_ListHeader'
    );
  
  /**
   * A map of lowercased header names to their normalized RFC 2822 form.
   * @var string[]
   * @access private
   */
  private $_keyMap = array();
  
  /**
   * Creates a new SimpleHeaderFactory.
   */
  public function __construct()
  {
    $this->_keyMap = array_change_key_case(
      array_combine(
        array_keys($this->_classMap),
        array_keys($this->_classMap)
        ),
        CASE_LOWER
      );
  }
  
  /**
   * Set the default character of produced Headers.
   * @param string $charset
   */
  public function setDefaultCharacterSet($charset)
  {
    $this->_charset = $charset;
  }
  
  /**
   * Set the default language of produced Headers.
   * @param string $lang
   */
  public function setDefaultLanguage($lang)
  {
    $this->_lang = $lang;
  }
  
  /**
   * Set the maximum length of all lines in the produced headers.
   * @param int $length
   */
  public function setMaxLineLength($length)
  {
    $this->_lineLength = (int) $length;
  }
  
  /**
   * Set the HeaderEncoder which implements Q (a variant on QP) encoding
   * according to RFC 2045.
   * @param Swift_Mime_HeaderEncoder $encoder
   */
  public function setQEncoder(Swift_Mime_HeaderEncoder $encoder)
  {
    $this->_encoders['Q'] = $encoder;
  }
  
  /**
   * Set the HeaderEncoder which implements B (base64) encoding according to
   * RFC 2045.
   * @param Swift_Mime_HeaderEncoder $encoder
   */
  public function setBEncoder(Swift_Mime_HeaderEncoder $encoder)
  {
    $this->_encoders['B'] = $encoder;
  }
  
  /**
   * Set the default encoding method used in the Headers (either Q or B).
   * @param string $method
   */
  public function setDefaultEncodingMethod($method)
  {
    $this->_encodingMethod = $method;
  }
  
  /**
   * Set the Encoder which encodes HeaderAttributes according to RFC 2231.
   * @param Swift_Encoder $encoder
   */
  public function setAttributeEncoder(Swift_Encoder $encoder)
  {
    $this->_attrEncoder = $encoder;
  }
  
  /**
   * Produce a Header based on the given string.
   * The string passed should be an entire MIME header.
   * @param string $string
   * @return Swift_Mime_Header
   */
  public function createHeaderFromString($string)
  {
    $string = rtrim($string, "\r\n");
    //
  }
  
  /**
   * Produce a HeaderAttribute from the given string.
   * @param string $string
   * @return Swift_Mime_HeaderAttribute
   */
  public function createAttributeFromString($string)
  {
  }
  
  /**
   * Create a Header with the following arguments.
   * The argument list can vary, but the first arg should always be the name
   * of the Header, followed by Header-specific args.
   * NOTE to implementors: func_get_args() is expected to be used here.
   * @param string $name
   * @param mixed $v1
   * @param mixed $v2...
   * @return Swift_Mime_Header
   */
  public function createHeader(/*$name, $v1, $v2,...*/)
  {
    $args = func_get_args();
    $name = $args[0];
    $lowerName = strtolower($name);
    if (array_key_exists($lowerName, $this->_keyMap))
    {
      $headerName = $this->_keyMap[$lowerName];
      $args[0] = $headerName;
      $headerClass = $this->_classMap[$headerName];
    }
    else
    {
      $headerName = $name;
      $headerClass = 'Swift_Mime_Header_UnstructuredHeader';
    }
    
    //Ack, really do this??
    if (!class_exists($headerClass))
    {
      $endName = preg_replace('/^.*_([^_]+)$/', '$1', $headerClass);
      require_once dirname(__FILE__) . '/../Header/' . $endName . '.php';
    }
    
    $reflector = new ReflectionClass($headerClass);
    $header = $reflector->newInstanceArgs($args);
    $header->setEncoder($this->_encoders[$this->_encodingMethod]);
    $header->setCharacterSet($this->_charset);
    $header->setMaxLineLength($this->_lineLength);
    
    return $header;
  }
  
  /**
   * Create a new HeaderAttribute with $name and $value.
   * @param string $name
   * @param string $value
   * @return Swift_Mime_HeaderAttribute
   */
  public function createAttribute($name, $value)
  {
  }
  
}
