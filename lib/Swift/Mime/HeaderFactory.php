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

require_once dirname(__FILE__) . '/Header.php';
require_once dirname(__FILE__) . '/HeaderEncoder.php';
require_once dirname(__FILE__) . '/../Encoder.php';


/**
 * A Factory for making Headers.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_HeaderFactory
{
  
  /**
   * Set the default character of produced Headers.
   * @param string $charset
   */
  public function setDefaultCharacterSet($charset);
  
  /**
   * Set the default language of produced Headers.
   * @param string $lang
   */
  public function setDefaultLanguage($lang);
  
  /**
   * Set the maximum length of all lines in the produced headers.
   * @param int $length
   */
  public function setMaxLineLength($length);
  
  /**
   * Set the HeaderEncoder which implements Q (a variant on QP) encoding
   * according to RFC 2045.
   * @param Swift_Mime_HeaderEncoder $encoder
   */
  public function setQEncoder(Swift_Mime_HeaderEncoder $encoder);
  
  /**
   * Set the HeaderEncoder which implements B (base64) encoding according to
   * RFC 2045.
   * @param Swift_Mime_HeaderEncoder $encoder
   */
  public function setBEncoder(Swift_Mime_HeaderEncoder $encoder);
  
  /**
   * Set the default encoding method used in the Headers (either Q or B).
   * @param string $method
   */
  public function setDefaultEncodingMethod($method);
  
  /**
   * Set the Encoder which encodes HeaderAttributes according to RFC 2231.
   * @param Swift_Encoder $encoder
   */
  public function setAttributeEncoder(Swift_Encoder $encoder);
  
  /**
   * Produce a Header based on the given string.
   * The string passed should be an entire MIME header.
   * @param string $string
   * @return Swift_Mime_Header
   */
  public function createHeaderFromString($string);
  
  /**
   * Produce a HeaderAttribute from the given string.
   * @param string $string
   * @return Swift_Mime_HeaderAttribute
   */
  public function createAttributeFromString($string);
  
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
  public function createHeader(/*$name, $v1, $v2,...*/);
  
  /**
   * Create a new HeaderAttribute with $name and $value.
   * @param string $name
   * @param string $value
   * @return Swift_Mime_HeaderAttribute
   */
  public function createAttribute($name, $value);
  
}
