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

/**
 * A Received (trace) Mime Header in Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_ReceivedHeader extends Swift_Mime_Header_DateHeader
{
  
  /**
   * Name-value pairs which appear in the Header.
   * @var array
   * @access private
   */
  private $_data = array();
  
  /**
   * The number of name-value pairs appearing on a single line.
   * @var int
   * @access private
   */
  private $_pairsPerLine = 2;
  
  /**
   * Creates a new ReceivedHeader with the given $name and $info.
   * @param string $name
   * @param int $timestamp, optional
   * @param string[] $data, optional
   */
  public function __construct($name, $timestamp = null, $data = array())
  {
    parent::__construct($name, $timestamp);
    
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
    $pairs = array();
    foreach ($data as $nvp)
    {
      if (!array_key_exists('name', $nvp))
      {
        continue;
      }
      
      if (!array_key_exists('value', $nvp))
      {
        $nvp['value'] = null;
      }
      
      if (!array_key_exists('comment', $nvp))
      {
        $nvp['comment'] = null;
      }
      
      $pairs[] = $nvp;
    }
    $this->_data = $pairs;
    $this->setCachedValue(null);
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
    $this->setCachedValue(null);
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
   * Set the value of this Header as a string.
   * The tokens in the string SHOULD comply with RFC 2822, 3.3.
   * The value will be parsed so {@link getTimestamp()} and {@link getData()}
   * return valid values.
   * @param string $value
   * @see __construct()
   * @see setTimestamp()
   * @see setData()
   * @see getValue()
   */
  public function setPreparedValue($value)
  {
    //Parse out the date first
    if (false !== $semiColonPos = strrpos($value, ';'))
    {
      $semiColonPos = strrpos($value, ';');
      $date = substr($value, $semiColonPos + 1);
      $nameValueList = substr($value, 0, $semiColonPos);
      try
      {
        parent::setPreparedValue($date);
      }
      catch (Exception $e) //Invalid date-time format
      {
        $this->setTimestamp(null);
      }
    }
    else
    {
      $nameValueList = $value;
    }
    
    //Then try and get name-val-pairs
    $nvps = array();
    $currentNvp = array();
    $expecting = 'name'; //or value
    while (strlen($nameValueList) > 0)
    {
      //Get rid of any preceding comments
      $nameValueList = $this->getHelper()->trimCFWS($nameValueList, 'left');
      switch ($expecting)
      {
        //Looks for a name
        case 'name':
          if (preg_match('/^' . $this->getHelper()->getGrammar('item-name') . '/D',
            $nameValueList, $matches))
          {
            $currentNvp['name'] = $matches[0];
            $nameValueList = substr($nameValueList, strlen($matches[0]));
            $expecting = 'value';
            break;
          }
          else
          {
            break 2; //Parse error, stop trying to interpret
          }
        //Look for a value
        case 'value':
          if (preg_match('/^' . $this->getHelper()->getGrammar('angle-addr') . '/D',
            $nameValueList, $matches))
          {
            $itemValue = $matches[0];
          }
          elseif (preg_match('/^' . $this->getHelper()->getGrammar('addr-spec') . '/D',
            $nameValueList, $matches))
          {
            $itemValue = $matches[0];
          }
          elseif (preg_match('/^' . $this->getHelper()->getGrammar('domain') . '/D',
            $nameValueList, $matches))
          {
            $itemValue = $matches[0];
          }
          elseif (preg_match('/^' . $this->getHelper()->getGrammar('msg-id') . '/D',
            $nameValueList, $matches))
          {
            $itemValue = $matches[0];
          }
          elseif (preg_match('/^' . $this->getHelper()->getGrammar('atom') . '/D',
            $nameValueList, $matches))
          {
            $itemValue = $matches[0];
          }
          else
          {
            break 2;
          }
          
          $nameValueList = substr($nameValueList, strlen($itemValue));
          
          //Get rid of whitespace
          $itemValue = trim($itemValue);
          
          //Try to parse a comment if found
          if (preg_match('/' . $this->getHelper()->getGrammar('comment') . '$/D',
            $itemValue, $matches))
          {
            $comment = $matches[0];
            $currentNvp['comment'] = substr($comment, 1, -1);
            $itemValue = substr(
              $itemValue, 0, strlen($itemValue) - strlen($matches[0])
              );
          }
          
          //Strip comments from the value
          $currentNvp['value'] = $this->getHelper()->trimCFWS($itemValue);
          
          //Set up for the next name-val-pair
          $nvps[] = $currentNvp;
          $currentNvp = array();
          $expecting = 'name';
          
          break;
      }
    }
    
    if (!empty($currentNvp))
    {
      $nvps[] = $currentNvp;
    }
    
    $this->setData($nvps);
    
    $this->setCachedValue($value);
  }
  
  /**
   * Get the string value of the body in this Header.
   * This is not necessarily RFC 2822 compliant since RFC 2821 specifically
   * prevents the "fixing" of invalid Received headers.
   * @return string
   * @see toString()
   */
  public function getPreparedValue()
  {
    if (!$this->getCachedValue())
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
      
      $this->setCachedValue(implode("\r\n ", $nvpStrings) . '; ' .
        parent::getPreparedValue()
        );
    }
    
    return $this->getCachedValue();
  }
  
  /**
   * Get this Header as a string.
   * @return string
   */
  public function toString()
  {
    return $this->getName() . ': ' . $this->getPreparedValue() . "\r\n";
  }
  
}
