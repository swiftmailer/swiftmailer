<?php

/*
 An attachment in Swift Mailer.
 
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

//@require 'Swift/Mime/SimpleMimeEntity.php';
//@require 'Swift/Mime/ContentEncoder.php';
//@require 'Swift/Mime/HeaderSet.php';
//@require 'Swift/FileStream.php';
//@require 'Swift/KeyCache.php';

/**
 * An attachment, in a multipart message.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Attachment extends Swift_Mime_SimpleMimeEntity
{
  
  public function __construct(Swift_Mime_HeaderSet $headers,
    Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache)
  {
    parent::__construct($headers, $encoder, $cache);
    $this->setDisposition('attachment');
    $this->setContentType('application/octet-stream');
  }
  
  public function getNestingLevel()
  {
    return self::LEVEL_ATTACHMENT;
  }
  
  public function getDisposition()
  {
    return $this->_getHeaderFieldModel('Content-Disposition');
  }
  
  public function setDisposition($disposition)
  {
    if (!$this->_setHeaderFieldModel('Content-Disposition', $disposition))
    {
      $this->getHeaders()->addParameterizedHeader(
        'Content-Disposition', $disposition
        );
    }
    return $this;
  }
  
  public function getFilename()
  {
    return $this->_getHeaderParameter('Content-Disposition', 'filename');
  }
  
  public function setFilename($filename)
  {
    $this->_setHeaderParameter('Content-Disposition', 'filename', $filename);
    $this->_setHeaderParameter('Content-Type', 'name', $filename);
    return $this;
  }
  
  public function getSize()
  {
    return $this->_getHeaderParameter('Content-Disposition', 'size');
  }
  
  public function setSize($size)
  {
    $this->_setHeaderParameter('Content-Disposition', 'size', $size);
    return $this;
  }
  
  public function setFile(Swift_FileStream $file)
  {
    $this->setFilename(basename($file->getPath()));
    $this->setBody($file);
    return $this;
  }
  
}
