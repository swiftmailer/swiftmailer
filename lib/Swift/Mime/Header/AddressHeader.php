<?php

/*
 An Address Mime Header in Swift Mailer.
 
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

require_once dirname(__FILE__) . '/MailboxHeader.php';
require_once dirname(__FILE__) . '/../HeaderEncoder.php';


/**
 * An Address MIME Header for something like To or Cc.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_AddressHeader
  extends Swift_Mime_Header_MailboxHeader
{
  
  /**
   * Groups of addresses with display names.
   * @var string[]
   * @access private
   */
  private $_groups = array();
  
  /**
   * Addresses which will be sent to, but which will not be displayed.
   * @var string[]
   * @access private
   */
  private $_hiddenAddresses = array();
  
  /**
   * Creates a new AddressHeader with $name and $address.
   * @param string $name of Header
   * @param string $address, optional
   * @param string $charset, optional
   * @param Swift_Mime_HeaderEncoder $encoder, optional
   */
  public function __construct($name, $addresses = null, $charset = null,
    Swift_Mime_HeaderEncoder $encoder = null)
  {
    parent::__construct($name, $addresses, $charset, $encoder);
  }
  
  /**
   * Get all plain email addresses in this Header.
   * The returned array includes all addresses, including those grouped/hidden.
   * @return string[]
   * @see getNameAddresses()
   */
  public function getAddresses()
  {
    return array_keys($this->getNameAddresses());
  }
  
  /**
   * Get all mailboxes in this Header as key=>value pairs.
   * The key is the address and the value is the name (or null if none set).
   * This method returns addresses included in groups and hidden addresses too.
   * See {@link Swift_Mime_Header_MailboxHeader::getNameAddresses()} for an example.
   * @return string[]
   * @see getAddresses()
   * @see getNameAddressStrings()
   */
  public function getNameAddresses()
  {
    $addresses = parent::getNameAddresses();
    foreach ($this->_groups as $group)
    {
      $addresses = array_merge($addresses, $group);
    }
    $addresses = array_merge($addresses, $this->_hiddenAddresses);
    return $addresses;
  }
  
  /**
   * Remove one or more addresses from this Header.
   * This method scans groups and hidden addresses and removes those too.
   * @param string|string[] $addresses
   */
  public function removeAddresses($addresses)
  {
    parent::removeAddresses($addresses);
    foreach ($addresses as $address)
    {
      foreach ($this->_groups as $name => $group)
      {
        unset($this->_groups[$name][$address]);
      }
      unset($this->_hiddenAddresses[$address]);
    }
    $this->setCachedValue(null);
  }
  
  /**
   * Defines a group of addresses which appear as a single unit in the Header.
   * This is a little-known RFC 2822 feature, apart from it's use in specifying
   * undisclosed-recipients.
   * The address list in the group can be empty if needs be.
   * Any email addresses appearing in this group which are already set in
   * the header by setAddresses(), setNameAddresses() or related methods will be
   * displayed in this group only.
   * @param string $groupName, e.g. undisclosed-recipients
   * @param string[] $mailboxes to add to the group
   * @param boolean $hidden, true if all addresses in group should not be displayed
   */
  public function defineGroup($groupName, $mailboxes = array(), $hidden = false)
  {
    $mailboxes = $this->normalizeMailboxes($mailboxes);
    $this->removeAddresses(array_keys($mailboxes));
    $this->_groups[$groupName] = $mailboxes;
    if ($hidden)
    {
      $this->setHiddenAddresses(array_keys($mailboxes));
    }
    $this->setCachedValue(null);
  }
  
  /**
   * Get all mailboxes defined for a given group.
   * @param string $groupName
   * @return string[]
   */
  public function getGroup($groupName)
  {
    if (array_key_exists($groupName, $this->_groups))
    {
      return $this->_groups[$groupName];
    }
    else
    {
      throw new Exception('No such group defined [' . $groupName . ']');
    }
  }
  
  /**
   * Remove a defined group (and all it's addresses) from this Header.
   * @param string $groupName
   */
  public function removeGroup($groupName)
  {
    unset($this->_groups[$groupName]);
    $this->setCachedValue(null);
  }
  
  /**
   * Set addresses which should not be displayed in the Header, but which
   * will be sent to.
   * If any of these addresses exist in the header already, or in groups they will
   * be moved into the hidden address list.
   * @param string|string[] $addresses
   */
  public function setHiddenAddresses($addresses)
  {
    $existingHidden = $this->_hiddenAddresses;
    $this->removeAddresses((array) $addresses);
    $this->_hiddenAddresses = $existingHidden;
    foreach ((array) $addresses as $address)
    {
      $this->_hiddenAddresses[$address] = null;
    }
    $this->setCachedValue(null);
  }
  
  /**
   * Get the string value of the body in this Header.
   * This is not necessarily RFC 2822 compliant since folding white space will
   * not be added at this stage (see {@link toString()} for that).
   * @return string
   * @see toString()
   */
  public function getFieldBody()
  {
    if (is_null($this->getCachedValue()))
    {
      $mailboxListString = parent::getFieldBody();
      $groupLists = array();
      foreach ($this->_groups as $groupName => $mailboxes)
      {
        $groupLists[] = $this->createDisplayNameString($groupName) . ':' .
          $this->createMailboxListString($mailboxes) . ';';
      }
      $groupListString = implode(', ', $groupLists);
    
      if (!empty($mailboxListString) && !empty($groupListString))
      {
        $ret = $groupListString . ', ' . $mailboxListString;
      }
      else
      {
        $ret = $groupListString . $mailboxListString; //Will just be either one
      }
    
      $this->setCachedValue($ret);
    }
    
    return $this->getCachedValue();
  }
  
}
