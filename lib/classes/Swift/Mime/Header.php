<?php

/*
 Header Interface in Swift Mailer.
 
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


/**
 * A MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_Header
{
  
  /** Text headers */
  const TYPE_TEXT = 2;
  
  /** Parameterized headers (text + params) */
  const TYPE_PARAMETERIZED = 6;

  /** Mailbox and address headers */
  const TYPE_MAILBOX = 8;
  
  /** Date and time headers */
  const TYPE_DATE = 16;
  
  /** Identification headers */
  const TYPE_ID = 32;
  
  /** Address path headers */
  const TYPE_PATH = 64;
  
  /**
   * Get the type of Header that this instance represents.
   * @return int
   * @see TYPE_TEXT, TYPE_PARAMETERIZED, TYPE_MAILBOX
   * @see TYPE_DATE, TYPE_ID, TYPE_PATH
   */
  public function getFieldType();
  
  /**
   * Set the model for the field body.
   * The actual types needed will vary depending upon the type of Header.
   * @param mixed $model
   */
  public function setFieldBodyModel($model);
  
  /**
   * Set the charset used when rendering the Header.
   * @param string $charset
   */
  public function setCharset($charset);
  
  /**
   * Get the model for the field body.
   * The return type depends on the specifics of the Header.
   * @return mixed
   */
  public function getFieldBodyModel();
  
  /**
   * Get the name of this header (e.g. Subject).
   * The name is an identifier and as such will be immutable.
   * @return string
   */
  public function getFieldName();
  
  /**
   * Get the field body, prepared for folding into a final header value.
   * @return string
   */
  public function getFieldBody();
  
  /**
   * Get this Header rendered as a compliant string.
   * @return string
   */
  public function toString();
  
}
