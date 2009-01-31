<?php

/*
 Decorator plugin in Swift Mailer.

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

//@require 'Swift/Events/SendListener.php';
//@require 'Swift/Events/SendEvent.php';

/**
 * Allows customization of Messages on-the-fly.
 * @package Swift
 * @subpackage Plugins
 * @author Chris Corbyn
 */
class Swift_Plugins_DecoratorPlugin implements Swift_Events_SendListener
{

  /**
   * The replacement map.
   * @var array
   * @access private
   */
  private $_replacements = array();

  /**
   * The body as it was before replacements.
   * @var string
   * @access private
   */
  private $_orginalBody;

  /**
   * The original subject of the message, before replacements.
   * @var string
   * @access private
   */
  private $_originalSubject;

  /**
   * Bodies of children before they are replaced.
   * @var array
   * @access private
   */
  private $_originalChildBodies = array();

  /**
   * The Message which was last replaced.
   * @var Swift_Mime_Message
   * @access private
   */
  private $_lastMessage;

  /**
   * Create a new DecoratorPlugin with $replacements.
   * @param array $replacments
   */
  public function __construct($replacements = array())
  {
    $this->_replacements = $replacements;
  }

  /**
   * Invoked immediately before the Message is sent.
   * @param Swift_Events_SendEvent $evt
   */
  public function beforeSendPerformed(Swift_Events_SendEvent $evt)
  {
    $message = $evt->getMessage();
    $this->_restoreMessage($message);
    $to = array_keys($message->getTo());
    $address = array_shift($to);
    if (isset($this->_replacements[$address]))
    {
      $replacements = $this->_replacements[$address];
      $body = $message->getBody();
      $search=array_keys($replacements);
      $replace=array_values($replacements);
      $bodyReplaced = str_replace(
        $search, $replace, $body
        );
      if ($body != $bodyReplaced)
      {
        $this->_originalBody = $body;
        $message->setBody($bodyReplaced);
      }
      $subject = $message->getSubject();
      $subjectReplaced = str_replace(
        $search, $replace, $subject
        );
      if ($subject != $subjectReplaced)
      {
        $this->_originalSubject = $subject;
        $message->setSubject($subjectReplaced);
      }
      $children = (array) $message->getChildren();
      foreach ($children as $child)
      {
        list($type, ) = sscanf($child->getContentType(), '%[^/]/%s');
        if ('text' == $type)
        {
          $body = $child->getBody();
          $bodyReplaced = str_replace(
            $search, $replace, $body
            );
          if ($body != $bodyReplaced)
          {
            $child->setBody($bodyReplaced);
            $this->_originalChildBodies[$child->getId()] = $body;
          }
        }
      }
      $this->_lastMessage = $message;
    }
  }

  /**
   * Invoked immediately after the Message is sent.
   * @param Swift_Events_SendEvent $evt
   */
  public function sendPerformed(Swift_Events_SendEvent $evt)
  {
    $this->_restoreMessage($evt->getMessage());
  }

  // -- Private methods

  /**
   * Restore a changed message back to its original state.
   * @param Swift_Mime_Message $message
   * @access private
   */
  private function _restoreMessage(Swift_Mime_Message $message)
  {
    if ($this->_lastMessage === $message)
    {
      if (isset($this->_originalBody))
      {
        $message->setBody($this->_originalBody);
        $this->_originalBody = null;
      }
      if (isset($this->_originalSubject))
      {
        $message->setSubject($this->_originalSubject);
        $this->_originalSubject = null;
      }
      if (!empty($this->_originalChildBodies))
      {
        $children = (array) $message->getChildren();
        foreach ($children as $child)
        {
          $id = $child->getId();
          if (array_key_exists($id, $this->_originalChildBodies))
          {
            $child->setBody($this->_originalChildBodies[$id]);
          }
        }
        $this->_originalChildBodies = array();
      }
      $this->_lastMessage = null;
    }
  }

}
