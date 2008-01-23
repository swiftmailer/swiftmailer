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
    
    if (!is_null($mailbox))
    {
      $this->setNameAddresses($mailbox);
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
   * $header->setNameAddresses(array(
   *  'chris@swiftmailer.org' => 'Chris Corbyn',
   *  'mark@swiftmailer.org' //No associated personal name
   *  ));
   * ?>
   * </code>
   * @param string|string[] $mailboxes
   * @see __construct()
   * @see setAddresses()
   * @see setValue()
   */
  public function setNameAddresses($mailboxes)
  {
    $this->_mailboxes = $this->normalizeMailboxes((array) $mailboxes);
    $this->setCachedValue(null); //Clear any cached value
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
   * print_r($header->getNameAddressStrings());
   * // array (
   * // 0 => Chris Corbyn <chris@swiftmailer.org>,
   * // 1 => Mark Corbyn <mark@swiftmailer.org>
   * // )
   * ?>
   * </code>
   * @return string[]
   * @see getNameAddresses()
   * @see toString()
   */
  public function getNameAddressStrings()
  {
    return $this->_createNameAddressStrings($this->getNameAddresses());
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
   * print_r($header->getNameAddresses());
   * // array (
   * // chris@swiftmailer.org => Chris Corbyn,
   * // mark@swiftmailer.org => Mark Corbyn
   * // )
   * ?>
   * </code>
   * @return string[]
   * @see getAddresses()
   * @see getNameAddressStrings()
   */
  public function getNameAddresses()
  {
    return $this->_mailboxes;
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
   * @see setNameAddresses()
   * @see setValue()
   */
  public function setAddresses($addresses)
  {
    return $this->setNameAddresses(array_values((array) $addresses));
  }
  
  /**
   * Get all email addresses in this Header.
   * @return string[]
   * @see getNameAddresses()
   */
  public function getAddresses()
  {
    return array_keys($this->_mailboxes);
  }
  
  /**
   * Remove one or more addresses from this Header.
   * @param string|string[] $addresses
   */
  public function removeAddresses($addresses)
  {
    $this->setCachedValue(null);
    foreach ((array) $addresses as $address)
    {
      unset($this->_mailboxes[$address]);
    }
  }
  
  /**
   * Set the value of this Header as a string.
   * The tokens in the string MUST comply with RFC 2822, 3.6.
   * The value will be parsed so {@link getNameAddresses()} and other related methods
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
   * @see setNameAddresses()
   * @see setAddresses()
   * @see getValue()
   */
  public function setPreparedValue($value)
  {
    $this->setNameAddresses($this->resolveNameAddresses($value));
    
    //We already know it's valid, no need to re-render
    $this->setCachedValue($value);
  }
  
  /**
   * Get the string value of the body in this Header.
   * This is not necessarily RFC 2822 compliant since folding white space will
   * not be added at this stage (see {@link toString()} for that).
   * @return string
   * @see toString()
   */
  public function getPreparedValue()
  {
    //Compute the string value of the header only if needed
    if (is_null($this->getCachedValue()))
    {
      $this->setCachedValue($this->createMailboxListString($this->_mailboxes));
    }
    return $this->getCachedValue();
  }
  
  // -- Points of extension
  
  /**
   * Normalizes a user-input list of mailboxes into consistent key=>value pairs.
   * @param string[] $mailboxes
   * @return string[]
   * @access protected
   */
  protected function normalizeMailboxes(array $mailboxes)
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
      $this->_assertValidAddress($address);
      $actualMailboxes[$address] = $name;
    }
    
    return $actualMailboxes;
  }
  
  /**
   * Parses a RFC 2822 compliant mailbox-list and returns a list of key=>value
   * pairs where (email => name).
   * @param string $stringList
   * @return string[]
   * @access protected
   */
  protected function resolveNameAddresses($stringList)
  {
    $trimmedList = $this->getHelper()->trimCFWS($stringList);
    if ('' == $trimmedList)
    {
      return array();
    }
    
    $mailboxes = array();
    
    $mailboxList = preg_split('/(?<!\\\\),/', $stringList);
    
    foreach ($mailboxList as $mailbox)
    {
      $mailboxParts = preg_split(
        '/(?=<' . $this->getHelper()->getGrammar('addr-spec') . '>)/',
        $mailbox
        );
      if (count($mailboxParts) == 2)
      {
         //Remove the < and >
        $address = substr($this->getHelper()->trimCFWS($mailboxParts[1]), 1, -1);
        $name = $this->decodeDisplayNameString($mailboxParts[0]);
      }
      else
      {
        $address = $this->getHelper()->trimCFWS($mailboxParts[0]);
        $name = null;
      }
      
      $this->_assertValidAddress($address);
      
      $mailboxes[$address] = $name;
    }
    
    return $mailboxes;
  }
  
  /**
   * Produces a compliant, formatted display-name based on the string given.
   * @param string $displayName as displayed
   * @param boolean $shorten the first line to make remove for header name
   * @return string
   * @access protected
   */
  protected function createDisplayNameString($displayName, $shorten = false)
  {
    return $this->getHelper()->createPhrase($this, $displayName,
      $this->getCharacterSet(), $this->getEncoder(), $shorten
      );
  }
  
  /**
   * Decode/parse out a RFC 2822 compliant display-name to get the actual
   * text value.
   * @param string $displayName
   * @return string
   * @access protected
   */
  protected function decodeDisplayNameString($displayName)
  {
    return $this->getHelper()->decodePhrase($displayName);
  }
  
  /**
   * Creates a string form of all the mailboxes in the passed array.
   * @param string[] $mailboxes
   * @return string
   * @access protected
   */
  protected function createMailboxListString(array $mailboxes)
  {
    return implode(', ', $this->_createNameAddressStrings($mailboxes));
  }
  
  // -- Private methods
  
  /**
   * Return an array of strings conforming the the name-addr spec of RFC 2822.
   * @param string[] $mailboxes
   * @return string[]
   * @access private
   */
  private function _createNameAddressStrings(array $mailboxes)
  {
    $strings = array();
    
    foreach ($mailboxes as $email => $name)
    {
      $mailboxStr = $email;
      if (!is_null($name))
      {
        $nameStr = $this->createDisplayNameString($name, empty($strings));
        $mailboxStr = $nameStr . ' <' . $mailboxStr . '>';
      }
      $strings[] = $mailboxStr;
    }
    
    return $strings;
  }
  
  /**
   * Throws an Exception if the address passed does not comply with RFC 2822.
   * @param string $address
   * @throws Exception If invalid.
   * @access protected
   */
  private function _assertValidAddress($address)
  {
    if (!preg_match('/^' . $this->getHelper()->getGrammar('addr-spec') . '$/D',
      $address))
    {
      throw new Exception(
        'Address in mailbox given does not comply with RFC 2822, 3.6.2.'
        );
    }
  }
  
}
