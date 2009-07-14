<?php

/*
 MimePart wrapper class in Swift Mailer.
 
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

//@require 'Swift/Mime/MimePart.php';
//@require 'Swift/DependencyContainer.php';

/**
 * A MIME part, in a multipart message.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_MimePart extends Swift_Mime_MimePart
{
  
  /**
   * Create a new MimePart.
   * Details may be optionally passed into the constructor.
   * @param string $body
   * @param string $contentType
   * @param string $charset
   */
  public function __construct($body = null, $contentType = null,
    $charset = null)
  {
    call_user_func_array(
      array($this, 'Swift_Mime_MimePart::__construct'),
      Swift_DependencyContainer::getInstance()
        ->createDependenciesFor('mime.part')
      );
    
    if (!isset($charset))
    {
      $charset = Swift_DependencyContainer::getInstance()
        ->lookup('properties.charset');
    }
    $this->setBody($body);
    $this->setCharset($charset);
    if ($contentType)
    {
      $this->setContentType($contentType);
    }
  }
  
  /**
   * Create a new MimePart.
   * @param string $body
   * @param string $contentType
   * @param string $charset
   * @return Swift_Mime_MimePart
   */
  public static function newInstance($body = null, $contentType = null,
    $charset = null)
  {
    return new self($body, $contentType, $charset);
  }
  
}
