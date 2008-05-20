<?php

/*
 The default Message class Swift Mailer.
 
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

//@require 'Swift/Mime/Message.php';
//@require 'Swift/Mime/MimePart.php';
//@require 'Swift/Mime/MimeEntity.php';
//@require 'Swift/Mime/HeaderSet.php';
//@require 'Swift/Mime/ContentEncoder.php';

/**
 * The default email message class.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_SimpleMessage extends Swift_Mime_MimePart
  implements Swift_Mime_Message
{
  
  public function __construct(Swift_Mime_HeaderSet $headers,
    Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache, $charset = null)
  {
    parent::__construct($headers, $encoder, $cache, $charset);
    $this->getHeaders()->defineOrdering(array(
      'Return-Path',
      'Sender',
      'Message-ID',
      'Date',
      'Subject',
      'From',
      'Reply-To',
      'To',
      'Cc',
      'Bcc',
      'MIME-Version',
      'Content-Type',
      'Content-Transfer-Encoding'
      ));
    $this->getHeaders()->setAlwaysDisplayed(
      array('Date', 'Message-ID', 'From')
      );
    $this->getHeaders()->addTextHeader('MIME-Version', '1.0');
    $this->setDate(time());
    $this->setId($this->getId());
    $this->getHeaders()->addMailboxHeader('From');
  }
  
  public function getNestingLevel()
  {
    return self::LEVEL_TOP;
  }
  
  public function setSubject($subject)
  {
    if (!$this->_setHeaderFieldModel('Subject', $subject))
    {
      $this->getHeaders()->addTextHeader('Subject', $subject);
    }
    return $this;
  }
  
  public function getSubject()
  {
    return $this->_getHeaderFieldModel('Subject');
  }
  
  public function setDate($date)
  {
    if (!$this->_setHeaderFieldModel('Date', $date))
    {
      $this->getHeaders()->addDateHeader('Date', $date);
    }
    return $this;
  }
  
  public function getDate()
  {
    return $this->_getHeaderFieldModel('Date');
  }
  
  public function setReturnPath($address)
  {
    if (!$this->_setHeaderFieldModel('Return-Path', $address))
    {
      $this->getHeaders()->addPathHeader('Return-Path', $address);
    }
    return $this;
  }
  
  public function getReturnPath()
  {
    return $this->_getHeaderFieldModel('Return-Path');
  }
  
  public function setSender($address)
  {
    if (!$this->_setHeaderFieldModel('Sender', (array) $address))
    {
      $this->getHeaders()->addMailboxHeader('Sender', (array) $address);
    }
    return $this;
  }
  
  public function getSender()
  {
    return $this->_getHeaderFieldModel('Sender');
  }
  
  public function setFrom($addresses)
  {
    if (!$this->_setHeaderFieldModel('From', (array) $addresses))
    {
      $this->getHeaders()->addMailboxHeader('From', (array) $addresses);
    }
    return $this;
  }
  
  public function getFrom()
  {
    return $this->_getHeaderFieldModel('From');
  }
  
  public function setReplyTo($addresses)
  {
    if (!$this->_setHeaderFieldModel('Reply-To', (array) $addresses))
    {
      $this->getHeaders()->addMailboxHeader('Reply-To', (array) $addresses);
    }
    return $this;
  }
  
  public function getReplyTo()
  {
    return $this->_getHeaderFieldModel('Reply-To');
  }
  
  public function setTo($addresses)
  {
    if (!$this->_setHeaderFieldModel('To', (array) $addresses))
    {
      $this->getHeaders()->addMailboxHeader('To', (array) $addresses);
    }
    return $this;
  }
  
  public function getTo()
  {
    return $this->_getHeaderFieldModel('To');
  }
  
  public function setCc($addresses)
  {
    if (!$this->_setHeaderFieldModel('Cc', (array) $addresses))
    {
      $this->getHeaders()->addMailboxHeader('Cc', (array) $addresses);
    }
    return $this;
  }
  
  public function getCc()
  {
    return $this->_getHeaderFieldModel('Cc');
  }
  
  public function setBcc($addresses)
  {
    if (!$this->_setHeaderFieldModel('Bcc', (array) $addresses))
    {
      $this->getHeaders()->addMailboxHeader('Bcc', (array) $addresses);
    }
    return $this;
  }
  
  public function getBcc()
  {
    return $this->_getHeaderFieldModel('Bcc');
  }
  
  public function setPriority($priority)
  {
    $priorityMap = array(
      1 => 'Highest',
      2 => 'High',
      3 => 'Normal',
      4 => 'Low',
      5 => 'Lowest'
      );
    if ($priority > max(array_keys($priorityMap)))
    {
      $priority = max(array_keys($priorityMap));
    }
    elseif ($priority < min(array_keys($priorityMap)))
    {
      $priority = min(array_keys($priorityMap));
    }
    if (!$this->_setHeaderFieldModel('X-Priority',
      sprintf('%d (%s)', $priority, $priorityMap[$priority])))
    {
      $this->getHeaders()->addTextHeader('X-Priority',
        sprintf('%d (%s)', $priority, $priorityMap[$priority]));
    }
    return $this;
  }
  
  public function getPriority()
  {
    list($priority) = sscanf($this->_getHeaderFieldModel('X-Priority'),
      '%[1-5]'
      );
    return isset($priority) ? $priority : 3;
  }
  
  public function setReadReceiptTo($addresses)
  {
    if (!$this->_setHeaderFieldModel('Disposition-Notification-To', $addresses))
    {
      $this->getHeaders()
        ->addMailboxHeader('Disposition-Notification-To', $addresses);
    }
    return $this;
  }
  
  public function getReadReceiptTo()
  {
    return $this->_getHeaderFieldModel('Disposition-Notification-To');
  }
  
  public function attach(Swift_Mime_MimeEntity $entity)
  {
    $this->setChildren(array_merge($this->getChildren(), array($entity)));
    return $this;
  }
  
  public function detach(Swift_Mime_MimeEntity $entity)
  {
    $newChildren = array();
    foreach ($this->getChildren() as $child)
    {
      if ($entity !== $child)
      {
        $newChildren[] = $child;
      }
    }
    $this->setChildren($newChildren);
    return $this;
  }
  
  public function embed(Swift_Mime_MimeEntity $entity)
  {
    $this->attach($entity);
    return 'cid:' . $entity->getId();
  }
  
  public function toString()
  {
    if (count($children = $this->getChildren()) > 0 && $this->getBody() != '')
    {
      $this->setChildren(array_merge(array($this->_becomeMimePart()), $children));
      $string = parent::toString();
      $this->setChildren($children);
    }
    else
    {
      $string = parent::toString();
    }
    return $string;
  }
  
  public function toByteStream(Swift_InputByteStream $is)
  {
    if (count($children = $this->getChildren()) > 0 && $this->getBody() != '')
    {
      $this->setChildren(array_merge(array($this->_becomeMimePart()), $children));
      parent::toByteStream($is);
      $this->setChildren($children);
    }
    else
    {
      parent::toByteStream($is);
    }
  }
  
  // -- Protected methods
  
  protected function _getIdField()
  {
    return 'Message-ID';
  }
  
  // -- Private methods
  
  private function _becomeMimePart()
  {
    $part = new parent($this->getHeaders()->newInstance(), $this->getEncoder(),
      $this->_getCache(), $this->_userCharset
      );
    $part->setContentType($this->_userContentType);
    $part->setBody($this->getBody());
    $part->setFormat($this->_userFormat);
    $part->setDelSp($this->_userDelSp);
    $part->_setNestingLevel($this->_getTopNestingLevel());
    return $part;
  }
  
  private function _getTopNestingLevel()
  {
    $highestLevel = $this->getNestingLevel();
    foreach ($this->getChildren() as $child)
    {
      $childLevel = $child->getNestingLevel();
      if ($highestLevel < $childLevel)
      {
        $highestLevel = $childLevel;
      }
    }
    return $highestLevel;
  }
  
}
