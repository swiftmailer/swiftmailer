<?php

/*
 A Mailbox Address Mime Header in Swift Mailer.
 
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

require_once dirname(__FILE__) . '/StructuredHeader.php';
require_once dirname(__FILE__) . '/../HeaderEncoder.php';


/**
 * A Mailbox Address MIME Header for something like From or Sender.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_MailboxHeader
  extends Swift_Mime_Header_StructuredHeader
{
  
  /**
   * The mailboxes used in this Header.
   * @var string[]
   * @access private
   */
  private $_mailboxes = array();
  
  /**
   * Special characters used in the syntax which need to be escaped.
   * @var string[]
   * @access private
   */
  private $_specials = array();
  
  /**
   * Creates a new MailboxHeader with $name and $mailbox.
   * @param string $name of Header
   * @param mixed $mailbox, optional as string or string[]
   * @param string $charset, optional
   * @param Swift_Mime_HeaderEncoder $encoder, optional
   */
  public function __construct($name, $mailbox = null, $charset = null,
    Swift_Mime_HeaderEncoder $encoder = null)
  {
    parent::__construct($name, null, $charset, $encoder);
                        
    $this->_specials = array(
      '\\', '(', ')', '<', '>', '[', ']',
      ':', ';', '@', ',', '.', '"'
      );
    
    if (is_array($mailbox))
    {
      $this->setMailboxes($mailbox);
    }
    elseif (!is_null($mailbox))
    {
      $this->setMailbox($mailbox);
    }
  }
  
  /**
   * Set a list of mailboxes to be shown in this Header.
   * The mailboxes can be a simple array of addresses, or an array of
   * name => value pairs where (email => personalName).
   * @param string[] $mailboxes
   */
  public function setMailboxes(array $mailboxes)
  {
    $actualMailboxes = array();
    
    foreach ($mailboxes as $key => $value)
    {
      if (is_string($key)) //key is email addr
      {
        $address = $key;
        $name = $value;
      }
      else
      {
        $address = $value;
        $name = null;
      }
      
      if (!preg_match('/^' . $this->rfc2822Tokens['addr-spec'] . '$/D',
        $address))
      {
        throw new Exception(
          'Address in mailbox given does not comply with RFC 2822, 3.6.2.'
          );
      }
      
      $actualMailboxes[$address] = $name;
    }
    
    $this->_mailboxes = $actualMailboxes;
  }
  
  /**
   * Set the mailbox address of this Header.
   * @param string $mailbox
   */
  public function setMailbox($mailbox)
  {
    return $this->setMailboxes((array)$mailbox);
  }
  
  /**
   * Get the full mailbox list of this Header as an array of valid strings.
   * @return string[]
   */
  public function getMailboxStrings()
  {
    $strings = array();
    
    foreach ($this->_mailboxes as $email => $name)
    {
      $mailboxStr = $email;
      if (!is_null($name))
      {
        //Treat name as exactly what was given
        $nameStr = $name;
        
        //If it's not valid
        if (!preg_match(
          '/^' . $this->rfc2822Tokens['display-name'] . '$/D',
          $nameStr))
        {
          // .. but it is just ascii text, try escaping some characters
          // and make it a quoted-string
          if (preg_match('/^' . $this->rfc2822Tokens['text'] . '*$/D', $nameStr))
          {
            foreach ($this->_specials as $char)
            {
              $nameStr = str_replace($char, '\\' . $char, $nameStr);
            }
            $nameStr = '"' . $nameStr . '"';
          }
          else // ... otherwise it needs encoding
          {
            $nameStr = $this->getTokenAsEncodedWord($name);
          }
        }
        
        $mailboxStr = $nameStr . ' <' . $mailboxStr . '>';
      }
      $strings[] = $mailboxStr;
    }
    
    return $strings;
  }
  
  /**
   * Get the full mailbox of this Header in its valid string form.
   * If multiple mailboxes are set in this Header, only the first is returned.
   * @return string
   */
  public function getMailboxString()
  {
    foreach ($this->getMailboxStrings() as $mailbox)
    {
      return $mailbox;
    }
  }
  
  /**
   * Get all mailboxes in this Header as key=>value pairs.
   * The key is the address and the value is the name (or null if none set).
   * @return string[]
   */
  public function getMailboxes()
  {
    return $this->_mailboxes;
  }
  
  /**
   * Get all addresses in this Header.
   * @return string[]
   */
  public function getAddresses()
  {
    return array_keys($this->_mailboxes);
  }
  
  /**
   * Get the address of the mailbox.
   * If multiple mailboxes are set in this header, only the first address is returned.
   * @return string
   */
  public function getAddress()
  {
    foreach ($this->getAddresses() as $address)
    {
      return $address;
    }
  }
  
}
