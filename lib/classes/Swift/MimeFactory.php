<?php

/*
 Abstract Dependency Injection factory for MIME components in Swift Mailer.
 
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

//@require 'Swift/Di.php';

/**
 * The abstract factory for making classes from the MIME subpackage.
 * @package Swift
 * @author Chris Corbyn
 */
abstract class Swift_MimeFactory extends Swift_Di
{
  
  /**
   * Singleton instance.
   * @var Swift_MimeFactory
   * @access private
   */
  private static $_instance = null;
  
  /**
   * Constructor cannot be used.
   * @access private
   */
  public function __construct()
  {
    $this->setLookup('charset', 'string:utf-8');
    $this->setLookup('cache', 'di:arraycache');
    $this->setLookup('temppath', 'string:/tmp');
    $this->setLookup('xheadername', 'string:X-Custom');
  }
  
  /**
   * Set the default character set for mime entities.
   * @param string $charset
   */
  public function setCharset($charset)
  {
    $this->setLookup('charset', 'string:' . $charset);
  }
  
  /**
   * The the path a writable directory which can be used for caching.
   * @param string $tmpPath
   */
  public function setTempPath($tmpPath)
  {
    $this->setLookup('temppath', 'string:' . $tmpPath);
  }
  
  /**
   * Set the type of cache used when rendering MIME entities.
   * @param string $cache alias name
   */
  public function setCacheType($cache)
  {
    $name = strtolower($cache);
    if (substr($name, -5) != 'cache')
    {
      $name .= 'cache';
    }
    if (array_key_exists($name, $this->getDependencyMap()))
    {
      $this->setLookup('cache', 'di:' . $name);
    }
    else
    {
      throw new Exception('Cache backend [' . $cache . '] does not exist.');
    }
  }
  
  /**
   * Get an instance as a singleton.
   * @return Swift_MimeFactory
   */
  public static function getInstance()
  {
    if (!isset(self::$_instance))
    {
      self::$_instance = parent::getInstance()->create('mimefactory');
    }
    return self::$_instance;
  }
  
  /**
   * Create a new MIME Header with $name, $value and $params.
   * @param string $name
   * @param string $value
   * @param string[] $params
   * @return Swift_Mime_Header
   */
  abstract public function createHeader($name = null, $value = null,
    $params = array());
    
  /**
   * Create a new Message for sending/adding content to.
   * @param string $subject
   * @param mixed $body
   * @param string $contentType
   * @param string $charset
   * @return Swift_Mime_Message
   */
  abstract public function createMessage($subject = null, $body = null,
    $contentType = null, $charset = null);
  
  /**
   * Create a new MIME part for nesting in a message.
   * @param mixed $body
   * @param string $contentType
   * @param string $charset
   * @return Swift_Mime_MimeEntity
   */
  abstract public function createPart($body = null, $contentType = null,
    $charset = null);
    
  /**
   * Create a new Attahment for nesting in a message.
   * @param mixed $data
   * @param string $filename
   * @param string $contentType
   * @return Swift_Mime_MimeEntity
   */
  abstract public function createAttachment($data = null, $filename = null,
    $contentType = null);
    
  /**
   * Create a new EmbeddedFile for nesting in a message.
   * @param mixed $data
   * @param string $filename
   * @param string $contentType
   * @return Swift_Mime_MimeEntity
   */
  abstract public function createEmbeddedFile($data = null, $filename = null,
    $contentType = null);
    
  /**
   * Create a new Image for nesting into a Message.
   * @param mixed $data
   * @param string $filename
   * @param string $contentType
   * @return Swift_Mime_MimeEntity
   */
  abstract public function createImage($data = null, $filename = null,
    $contentType = null);
  
}
