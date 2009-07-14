<?php

/*
 EmbeddedFile wrapper class in Swift Mailer.
 
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

//@require 'Swift/Mime/Attachment.php';
//@require 'Swift/DependencyContainer.php';
//@require 'Swift/ByteStream/FileByteStream.php';

/**
 * An embedded file, in a multipart message.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_EmbeddedFile extends Swift_Mime_EmbeddedFile
{
  
  /**
   * Create a new EmbeddedFile.
   * Details may be optionally provided to the constructor.
   * @param string|Swift_OutputByteStream $data
   * @param string $filename
   * @param string $contentType
   */
  public function __construct($data = null, $filename = null,
    $contentType = null)
  {
    call_user_func_array(
      array($this, 'Swift_Mime_EmbeddedFile::__construct'),
      Swift_DependencyContainer::getInstance()
        ->createDependenciesFor('mime.embeddedfile')
      );
    
    $this->setBody($data);
    $this->setFilename($filename);
    if ($contentType)
    {
      $this->setContentType($contentType);
    }
  }
  
  /**
   * Create a new EmbeddedFile.
   * @param string|Swift_OutputByteStream $data
   * @param string $filename
   * @param string $contentType
   * @return Swift_Mime_EmbeddedFile
   */
  public static function newInstance($data = null, $filename = null,
    $contentType = null)
  {
    return new self($data, $filename, $contentType);
  }
  
  /**
   * Create a new EmbeddedFile from a filesystem path.
   * @param string $path
   * @return Swift_Mime_EmbeddedFile
   */
  public static function fromPath($path)
  {
    return self::newInstance()->setFile(
      new Swift_ByteStream_FileByteStream($path)
      );
  }
  
}
