<?php

/*
 An abstract base MIME Header in Swift Mailer.
 
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

require_once dirname(__FILE__) . '/UnstructuredHeader.php';
require_once dirname(__FILE__) . '/../HeaderEncoder.php';
require_once dirname(__FILE__) . '/../FieldChangeObserver.php';
require_once dirname(__FILE__) . '/../../Encoder.php';


/**
 * An abstract base MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_ParameterizedHeader
  extends Swift_Mime_Header_UnstructuredHeader
  implements Swift_Mime_FieldChangeObserver
{
  
  /**
   * The Encoder used to encode the parameters.
   * @var Swift_Encoder
   * @access private
   */
  private $_paramEncoder;
  
  /**
   * The parameters as an associative array.
   * @var string[]
   * @access private
   */
  private $_params = array();
  
  /**
   * RFC 2231's definition of a token.
   * @var string
   * @access private
   */
  private $_tokenRe;
  
  /**
   * Creates a new ParameterizedHeader with $name.
   * @param string $name
   * @param Swift_Mime_HeaderEncoder $encoder
   * @param Swift_Encoder $paramEncoder
   */ 
  public function __construct($name, Swift_Mime_HeaderEncoder $encoder,
    Swift_Encoder $paramEncoder)
  {
    $this->setFieldName($name);
    $this->setEncoder($encoder);
    $this->_paramEncoder = $paramEncoder;
    $this->initializeGrammar();
    $this->_tokenRe = '(?:[\x21\x23-\x27\x2A\x2B\x2D\x2E\x30-\x39\x41-\x5A\x5E-\x7E]+)';
  }
  
  /**
   * Set an associative array of parameter names mapped to values.
   * @param string[]
   */
  public function setParameters(array $parameters)
  {
    $this->_params = $parameters;
  }
  
  /**
   * Returns an associative array of parameter names mapped to values.
   * @return string[]
   */
  public function getParameters()
  {
    return $this->_params;
  }
  
  /**
   * Get the value of this header prepared for rendering.
   * @return string
   */
  public function getFieldBody()
  {
    $body = parent::getFieldBody();
    foreach ($this->_params as $name => $value)
    {
      //Add the parameter
      $body .= '; ' . $this->_createParameter($name, $value);
    }
    
    return $body;
  }
  
  /**
   * Notify this observer that a field has changed to $value.
   * "Field" is a loose term and refers to class fields rather than
   * header fields.  $field will always be in lowercase and will be alpha.
   * only.
   * An example could be fieldChanged('contenttype', 'text/plain');
   * This of course reflects a change in the body of the Content-Type header.
   * Another example could be fieldChanged('charset', 'us-ascii');
   * This reflects a change in the charset parameter of the Content-Type header.
   * @param string $field in lowercase ALPHA
   * @param mixed $value
   */
  public function fieldChanged($field, $value)
  {
    $fieldName = strtolower($this->getFieldName());
    
    if ('content-type' == $fieldName)
    {
      $parameters = $this->getParameters();
      switch ($field)
      {
        case 'contenttype':
          $this->setValue($value);
          break;
        case 'delsp':
          $value = $value ? 'yes' : 'no';
        case 'charset':
        case 'boundary':
        case 'format':
          $parameters[$field] = $value;
          $this->setParameters($parameters);
          break;
      }
    }
  }
  
  // -- Protected methods
  
  /**
   * Generate a list of all tokens in the final header.
   * This doesn't need to be overridden in theory, but it is for implementation
   * reasons to prevent potential breakage of attributes.
   * @return string[]
   * @access protected
   */
  protected function toTokens($string = null)
  {
    $tokens = parent::toTokens(parent::getFieldBody());
    
    //Try creating any parameters
    foreach ($this->_params as $name => $value)
    {
      //Add the semi-colon separator
      $tokens[count($tokens)-1] .= ';';
      $tokens = array_merge($tokens, $this->generateTokenLines(
        ' ' . $this->_createParameter($name, $value)
        ));
    }
    
    return $tokens;
  }
  
  // -- Private methods
  
  /**
   * Render a RFC 2047 compliant header parameter from the $name and $value.
   * @param string $name
   * @param string $value
   * @return string
   * @access private
   */
  private function _createParameter($name, $value)
  {
    $origValue = $value;
    
    $needsEncoding = false;
    //Allow room for parameter name, indices, "=" and DQUOTEs
    $maxValueLength = $this->getMaxLineLength() - strlen($name . '=*N"";') - 1;
    $firstLineOffset = 0;
    
    //If it's not already a valid parameter value...
    if (!preg_match('/^' . $this->_tokenRe . '$/D', $value))
    {
      //TODO: text, or something else??
      //... and it's not ascii
      if (!preg_match('/^' . $this->getGrammar('text') . '*$/D', $value))
      {
        $needsEncoding = true;
        //Allow space for the indices, charset and language
        $maxValueLength = $this->getMaxLineLength() - strlen($name . '*N*="";') - 1;
        $firstLineOffset = strlen(
          $this->getCharset() . "'" . $this->getLanguage() . "'"
          );
      }
    }
    
    //Encode if we need to
    if ($needsEncoding || strlen($value) > $maxValueLength)
    {
      $value = $this->_paramEncoder->encodeString(
        $origValue, $firstLineOffset, $maxValueLength
      );
    }
    
    $valueLines = explode("\r\n", $value);
    
    //Need to add indices
    if (count($valueLines) > 1)
    {
      $paramLines = array();
      foreach ($valueLines as $i => $line)
      {
        $paramLines[] = $name . '*' . $i .
          $this->_getEndOfParameterValue($line, ($needsEncoding && $i == 0));
      }
      return implode(";\r\n ", $paramLines);
    }
    else
    {
      return $name . $this->_getEndOfParameterValue(
        $valueLines[0], $needsEncoding
        );
    }
  }
  
  /**
   * Returns the parameter value from the "=" and beyond.
   * @param string $value to append
   * @param boolean $addEncodingInfo
   * @return string
   * @access private
   */
  private function _getEndOfParameterValue($value, $addEncodingInfo = false)
  {
    if (!preg_match('/^' . $this->_tokenRe . '$/D', $value))
    {
      $value = '"' . $value . '"';
    }
    if ($addEncodingInfo)
    {
      return '*=' . $this->getCharset() . "'" . $this->getLanguage() .
        "'" . $value;
    }
    else
    {
      return '=' . $value;
    }
  }
  
}
