<?php

/*
 An ID Mime Header in Swift Mailer.
 
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

/**
 * An ID MIME Header for something like Message-ID or Content-ID.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_IdentificationHeader
  extends Swift_Mime_Header_StructuredHeader
{
  
  /**
   * The IDs used in the value of this Header.
   * This may hold multiple IDs or just a single ID.
   * @var string[]
   * @access private
   */
  private $_ids = array();
  
  /**
   * Creates a new IdentificationHeader with the given $name and $id.
   * @param string $name
   * @param mixed $id, optional as string or string[]
   */
  public function __construct($name, $id = null)
  {
    parent::__construct($name);
    
    if (is_array($id))
    {
      $this->setIds($id);
    }
    elseif (!is_null($id))
    {
      $this->setId($id);
    }
  }
  
  /**
   * Sets the Value of the Header explicitly.
   * It's not recommended to use this method, though input will be validated.
   * @param string $value, complying to RFC 2822, 3.6.
   */
  public function setValue($value)
  {
    $ids = $this->_getIdsFromValue($value);
    $this->_ids = $ids;
    parent::setValue($value);
  }
  
  /**
   * Set the ID used in the value of this header.
   * @param string $id
   */
  public function setId($id)
  {
    return $this->setIds(array($id));
  }
  
  /**
   * Get the ID used in the value of this Header.
   * If multiple IDs are set only the first is returned.
   * @return string
   */
  public function getId()
  {
    if (count($this->_ids) > 0)
    {
      return $this->_ids[0];
    }
  }
  
  /**
   * Set a collection of IDs to use in the value of this Header.
   * @param string[] $ids
   */
  public function setIds(array $ids)
  {
    $actualIds = array();
    $angleAddrs = array();
    
    foreach ($ids as $k => $id)
    {
      if (preg_match(
        '/^' . $this->getHelper()->getGrammar('id-left') . '@' .
        $this->getHelper()->getGrammar('id-right') . '$/D',
        $id
        ))
      {
        $angleAddrs[] = '<' . $id . '>';
        $actualIds[] = $id;
      }
      else //Try assumng full ID spec incl any CFWS according to RFC 2822
      {
        $idList = $this->_getIdsFromValue($id);
        $actualIds = array_merge($actualIds, $idList);
        $angleAddrs[] = $id;
      }
    }
    
    $this->_ids = $actualIds;
    return parent::setValue(implode(' ', $angleAddrs));
  }
  
  /**
   * Get the list of IDs used in this Header.
   * @return string[]
   */
  public function getIds()
  {
    return $this->_ids;
  }
  
  /**
   * Sets the value of this Header as if it's already been prepared for use.
   * Lines needn't be folded since {@link toString()} will fold long lines.
   * @param string $value
   */
  public function setPreparedValue($value)
  {
    return $this->setValue($value);
  }
  
  /**
   * Get the value prepared and ready for folding into to Header.
   * @return string
   */
  public function getPreparedValue()
  {
    return $this->getValue();
  }
  
  // -- Private methods
  
  /**
   * Parses an identification field value and returns the IDs from it.
   * The < and > angle brackets are not included in the returned array.
   * @param string $value
   * @return string[]
   * @throws Exception If the value does not comply with RFC 2822
   */
  private function _getIdsFromValue($value)
  {
    $ids = array();
    
    //Shouldn't really need this first CFWS!!! :-\
    $angleAddrs = preg_split(
      '/(?<=>)' . $this->getHelper()->getGrammar('CFWS') . '?(?=<)/',
      $value
      );
    
    foreach ($angleAddrs as $idToken)
    {
      if (preg_match('/^' . $this->getHelper()->getGrammar('msg-id') . '$/D', $idToken))
      {
        //Remove CFWS from start and end, then remove the < and >
        $ids[] = substr($this->getHelper()->trimCFWS($idToken), 1, -1);
      }
      else
      {
        throw new Exception(
          'Value of ID does not comply with RFC 2822, Section 3.6.4.'
          );
      }
    }
    
    return $ids;
  }
  
}
