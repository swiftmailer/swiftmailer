<?php

/*
 A Received (trace) Mime Header in Swift Mailer.
 
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

require_once dirname(__FILE__) . '/DateHeader.php';
require_once dirname(__FILE__) . '/../HeaderEncoder.php';

/**
 * A Received (trace) Mime Header in Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_ReceivedHeader extends Swift_Mime_Header_DateHeader
{
  
  private $_data = array();
  private $_pairsPerLine = 2;
  
  /**
   * Creates a new ReceivedHeader with the given $name and $info.
   * @param string $name
   * @param int $timestamp, optional
   * @param string[] $data, optional
   * @param string $charset, optional
   * @param Swift_Mime_HeaderEncoder $encoder, optional
   */
  public function __construct($name, $timestamp = null, $data = array(),
    $charset = null, Swift_Mime_HeaderEncoder $encoder = null)
  {
    parent::__construct($name, $timestamp, $charset, $encoder);
    $this->setData($data);
  }
  
  /**
   * Set name value pair data in the Header.
   * This method takes a list of associative arrays, each of which containing
   * the keys 'name' and 'value', and optionally a 'comment' key.
   * Example:
   * <code>
   * <?php
   * $header->setData(array(
   *  array('name' => 'by', 'value' => 'server.tld'),
   *  array('name' => 'with', 'value' => 'ESMTP', 'comment' => 'Using SupaDupaMail 1.3')
   *  ));
   * ?>
   * </code>
   * @param array $data
   * @see setPairsPerLine()
   * @see setTimestamp()
   * @see setValue()
   */
  public function setData(array $data)
  {
    foreach ($data as &$nvp)
    {
      if (!array_key_exists('comment', $nvp))
      {
        $nvp['comment'] = null;
      }
    }
    $this->_data = $data;
  }
  
  /**
   * Get the list of name-value pairs in the Header.
   * The returned value is an array of associative arrays, each of which
   * contains the keys 'name', 'value' and 'comment'.
   * A comment with a value of NULL means it's not set.
   * @return array
   * @see setData()
   * @see getTimestamp()
   * @see getValue()
   */
  public function getData()
  {
    return $this->_data;
  }
  
  /**
   * Set the number of name-value pairs which can appear on any same line.
   * The default is two (a reasonable default).
   * WARNING: If lines are longer than the value returned by {@link getLineLength()}
   * folding whitespace will be added.
   * @param int $num
   */
  public function setPairsPerLine($num)
  {
    $this->_pairsPerLine = (int) $num;
  }
  
  /**
   * Get the maximum number of name-value paris which may appear on any one line.
   * @return int
   * @see setPairsPerLine()
   */
  public function getPairsPerLine()
  {
    return $this->_pairsPerLine;
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
    $nvpStrings = array();
    $currentPairs = array();
    foreach ($this->_data as $i => $nvp)
    {
      //Force a new line if reached max number of name-value pairs on this line
      if ((0 != $i) && 0 == ($i % $this->_pairsPerLine))
      {
        $nvpStrings[] = implode(' ', $currentPairs);
        $currentPairs = array();
      }
      
      $nvpString = $nvp['name'] . ' ' . $nvp['value'];
      if (!is_null($nvp['comment']))
      {
        $nvpString .= ' (' . $nvp['comment'] . ')';
      }
      $currentPairs[] = $nvpString;
    }
    if (!empty($currentPairs))
    {
      $nvpStrings[] = implode(' ', $currentPairs);
    }
    return implode("\r\n ", $nvpStrings) . '; ' . parent::getValue();
  }
  
}
