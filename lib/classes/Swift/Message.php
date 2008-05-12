<?php

/*
 Message wrapper class Swift Mailer.
 
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

//@require 'Swift/Mime/SimpleMessage.php';
//@require 'Swift/DependencyContainer.php';

/**
 * The main user-facing Message wrapper.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Message extends Swift_Mime_SimpleMessage
{
  
  public function __construct($subject = null, $body = null,
    $contentType = null, $charset = null)
  {
    parent::__construct(
      Swift_DependencyContainer::getInstance()->lookup('mime.headerset'),
      Swift_DependencyContainer::getInstance()->lookup('mime.qpcontentencoder'),
      Swift_DependencyContainer::getInstance()->lookup('properties.cache')
      );
    
    if (!isset($charset))
    {
      $charset = Swift_DependencyContainer::getInstance()->lookup('properties.charset');
    }
    $this->setSubject($subject);
    $this->setBody($body);
    $this->setCharset($charset);
    if ($contentType)
    {
      $this->setContentType($contentType);
    }
  }
  
  public static function newInstance($subject = null, $body = null,
    $contentType = null, $charset = null)
  {
    return new self($subject, $body, $contentType, $charset);
  }
  
}
