<?php

/*
 A MIME Version Header in Swift Mailer.
 
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
 * A MIME Version Header for Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_VersionHeader
  extends Swift_Mime_Header_StructuredHeader
{
  
  /**
   * The version stored in this Header.
   * @var string
   * @access private
   */
  private $_version;
  
  /**
   * Creates a new VersionHeader with $name and $version.
   * Example:
   * <code>
   * <?php
   * $header = new Swift_Mime_Header_VersioinHeader('MIME-Version', '1.0');
   * ?>
   * </code>
   * @param string $name of Header
   * @param int $version, optional
   */
  public function __construct($name, $version = null)
  {
    parent::__construct($name);
    
    if (!is_null($version))
    {
      $this->setVersion($version);
    }
  }
  
  /**
   * Get the version stored in this Header.
   * @return string
   */
  public function getVersion()
  {
    return $this->_version;
  }
  
  /**
   * Set the version stored in this Header.
   * @param string $version
   */
  public function setVersion($version)
  {
    if (!preg_match('/^[0-9]+\.[0-9]+$/D', $version))
    {
      throw new Exception(
        'MIME-Version must be represented by dot-separated DIGITS according to RFC 2045, 4.'
        );
    }
    $this->_version = $version;
    $this->setCachedValue($version);
  }
  
  /**
   * Set the value of this Header as a string.
   * The tokens in the string MUST comply with RFC 2045, 4.
   * The value will be parsed so {@link getVersion()} returns a valid value.
   * @param string $value
   * @see __construct()
   * @see setVersion()
   * @see getValue()
   */
  public function setValue($value)
  {
    $version = $this->getHelper()->stripCFWS($value);
    $this->setVersion($version);
    $this->setCachedValue($value);
  }
  
  /**
   * Get the string value of the body in this Header.
   * This is not necessarily RFC 2822 compliant since folding whitespace may not
   * have been added. See {@link toString()} for that.
   * @return string
   * @see toString()
   */
  public function getValue()
  {
    return $this->getCachedValue();
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
