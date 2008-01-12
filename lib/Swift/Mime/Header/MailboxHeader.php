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
   * The value of this Header, cached.
   * @var string
   * @access private
   */
  private $_cachedValue = null;
  
  /**
   * Creates a new MailboxHeader with $name and $mailbox.
   * Example:
   * <code>
   * <?php
   * //Create a header with a single email
   * $header = new Swift_Mime_Header_MailboxHeader('Sender', 'chris@swiftmailer.org');
   * //Create a header with multiple emails
   * $header = new Swift_Mime_Header_MailboxHeader('From',
   *  array('chris@swiftmailer.org', 'mark@swiftmailer.org')
   *  );
   * //Create a header with a name included
   * $header = new Swift_Mime_Header_MailboxHeader('Sender',
   *  array('chris@swiftmailer.org' => 'Chris Corbyn')
   *  );
   * //Create a Header with multiple names and emails
   * $header = new Swift_Mime_Header_MailboxHeader('From',
   *  array('chris@swiftmailer.org' => 'Chris Corbyn',
   *  'mark@swiftmailer.org' => 'Mark Corbyn')
   *  );
   * //Create a Header with a mixture of emails and name-emails
   * $header = new Swift_Mime_Header_MailboxHeader('From',
   *  array('chris@swiftmailer.org', //No associated name
   *  'mark@swiftmailer.org' => 'Mark Corbyn')
   *  );
   * ?>
   * </code>
   * @param string $name of Header
   * @param string|string[] $mailbox, optional
   * @param string $charset, optional
   * @param Swift_Mime_HeaderEncoder $encoder, optional
   */
  public function __construct($name, $mailbox = null, $charset = null,
    Swift_Mime_HeaderEncoder $encoder = null)
  {
    parent::__construct($name, null, $charset, $encoder);
    
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
   * key=>value pairs where (email => personalName).
   * Example:
   * <code>
   * <?php
   * //Sets two mailboxes in the Header, one with a personal name
   * $header->setMailboxes(array(
   *  'chris@swiftmailer.org' => 'Chris Corbyn',
   *  'mark@swiftmailer.org' //No associated personal name
   *  ));
   * ?>
   * </code>
   * @param string[] $mailboxes
   * @see __construct()
   * @see setMailbox()
   * @see setAddress(), setAddresses()
   * @see setValue()
   */
  public function setMailboxes(array $mailboxes)
  {
    $this->_cachedValue = null; //Clear any cached value
    
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
   * Example:
   * <code>
   * <?php
   * //Set a plain address
   * $header->setMailbox('chris@swiftmailer.org');
   * //Set an address with a personal name
   * $header->setMailbox(array('chris@swiftmailer.org' => 'Chris Corbyn'));
   * ?>
   * </code>
   * @param string|string[] $mailbox as string $address or string[] mailbox with email=>name
   * @see __construct()
   * @see setMailboxes()
   * @see setAddress(), setAddresses()
   * @see setValue()
   */
  public function setMailbox($mailbox)
  {
    return $this->setMailboxes((array)$mailbox);
  }
  
  /**
   * Get the full mailbox list of this Header as an array of valid RFC 2822 strings.
   * Example:
   * <code>
   * <?php
   * $header = new Swift_Mime_Header_MailboxHeader('From',
   *  array('chris@swiftmailer.org' => 'Chris Corbyn',
   *  'mark@swiftmailer.org' => 'Mark Corbyn')
   *  );
   * print_r($header->getMailboxStrings());
   * // array (
   * // 0 => Chris Corbyn <chris@swiftmailer.org>,
   * // 1 => Mark Corbyn <mark@swiftmailer.org>
   * // )
   * ?>
   * </code>
   * @return string[]
   * @see getMailboxString()
   * @see getMailboxes()
   * @see toString()
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
            $nameStr = $this->escapeSpecials($nameStr);
            $nameStr = '"' . $nameStr . '"';
          }
          else // ... otherwise it needs encoding
          {
            //Determine space remaining on line if first line
            if (empty($strings))
            {
              $usedLength = strlen($this->getName() . ': ');
            }
            else
            {
              $usedLength = 0;
            }
            $nameStr = $this->getTokenAsEncodedWord($name, $usedLength);
          }
        }
        
        $mailboxStr = $nameStr . ' <' . $mailboxStr . '>';
      }
      $strings[] = $mailboxStr;
    }
    
    return $strings;
  }
  
  /**
   * Get the full mailbox of this Header in its valid RFC 2822 string form.
   * If multiple mailboxes are set in this Header, only the first is returned.
   * @return string
   * @see getMailboxStrings()
   * @see getMailboxes()
   * @see toString()
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
   * Example:
   * <code>
   * <?php
   * $header = new Swift_Mime_Header_MailboxHeader('From',
   *  array('chris@swiftmailer.org' => 'Chris Corbyn',
   *  'mark@swiftmailer.org' => 'Mark Corbyn')
   *  );
   * print_r($header->getMailboxes());
   * // array (
   * // chris@swiftmailer.org => Chris Corbyn,
   * // mark@swiftmailer.org => Mark Corbyn
   * // )
   * ?>
   * </code>
   * @return string[]
   * @see getAddress(), getAddresses()
   * @see getMailboxStrings()
   */
  public function getMailboxes()
  {
    return $this->_mailboxes;
  }
  
  /**
   * Simply makes this header represent a single address with no associated name.
   * Example:
   * <code>
   * <?php
   * $header->setAddress('chris@swiftmailer.org');
   * ?>
   * </code>
   * @param string $address
   * @see __construct()
   * @see setAddresses()
   * @see setMailbox(), setMailboxes()
   * @see setValue()
   */
  public function setAddress($address)
  {
    return $this->setMailbox($address);
  }
  
  /**
   * Makes this Header represent a list of plain email addresses with no names.
   * Example:
   * <code>
   * <?php
   * //Sets three email addresses as the Header data
   * $header->setAddresses(
   *  array('one@domain.tld', 'two@domain.tld', 'three@domain.tld')
   *  );
   * ?>
   * </code>
   * @param string[] $addresses
   * @see setAddress()
   * @see setMailbox(), setMailboxes()
   * @see setValue()
   */
  public function setAddresses(array $addresses)
  {
    return $this->setMailboxes(array_values($addresses));
  }
  
  /**
   * Get all email addresses in this Header.
   * @return string[]
   * @see getAddress()
   * @see getMailboxes()
   */
  public function getAddresses()
  {
    return array_keys($this->_mailboxes);
  }
  
  /**
   * Get the just the email address of the mailbox.
   * If multiple mailboxes are set in this header, only the first address is returned.
   * @return string
   * @see getAddresses()
   * @see getMailboxes()
   */
  public function getAddress()
  {
    foreach ($this->getAddresses() as $address)
    {
      return $address;
    }
  }
  
  /**
   * Set the value of this Header as a string.
   * The tokens in the string MUST comply with RFC 2822, 3.6.2.
   * The value will be parsed so {@link getMailboxes()} and other related methods
   * return appropriate values. This can be useful if working with raw data
   * from an email not generated by Swift.
   * Example:
   * <code>
   * <?php
   * //Sets two mailboxes is the Header. 
   * $header->setValue('Person Name <person@address.com>,
   *  (This is a comment) "Person 2\\, BSc\\." <person2@address.com>'
   *  );
   * ?>
   * </code>
   * @param string $value
   * @see __construct()
   * @see setMailbox(), setMailboxes()
   * @see setAddress(), setAddresses()
   * @see getValue()
   */
  public function setValue($value)
  {
    $mailboxes = array();
    
    $mailboxList = preg_split('/(?<!\\\\),/', $value);
    
    foreach ($mailboxList as $mailbox)
    {
      $mailboxParts = preg_split(
        '/(?=<' . $this->rfc2822Tokens['addr-spec'] . '>)/',
        $mailbox
        );
      if (count($mailboxParts) == 2)
      {
         //Remove the < and >
        $address = substr($this->trimCFWS($mailboxParts[1]), 1, -1);
        //Get rid of any CFWS
        $name = $this->trimCFWS($mailboxParts[0]);
        if ('' == $name)
        {
          $name = null;
        }
        elseif (substr($name, 0, 1) == '"') //Name is a quoted-string
        {
          $name = preg_replace('/\\\\(.)/', '$1', substr($name, 1, -1));
        }
        else //Name is a simple list of words
        {
          $name = $this->decodeEncodedWords($name);
        }
      }
      else
      {
        $address = $mailboxParts[0];
        $name = null;
      }
      $mailboxes[$address] = $name;
    }
    
    $this->setMailboxes($mailboxes);
    
    //We already know it's valid, no need to re-render
    $this->_cachedValue = $value;
  }
  
  /**
   * Get the string value of the body in this Header.
   * This is not necessarily RFC 2822 compliant since folding white space will
   * not be added at this stage (see {@link toString()} for that).
   * @return string
   * @see toString()
   */
  public function getValue()
  {
    //Compute the string value of the header only if needed
    if (is_null($this->_cachedValue))
    {
      $this->_cachedValue = implode(', ', $this->getMailboxStrings());
    }
    return $this->_cachedValue;
  }
  
  // -- Overridden points of extension
  
  /**
   * Gets the value with all needed tokens prepared for insertion into the Header.
   * @return string
   * @access protected
   */
  protected function getPreparedValue()
  {
    return $this->getValue();
  }
  
}
