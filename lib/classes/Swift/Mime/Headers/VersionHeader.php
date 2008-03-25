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

//@require 'Swift/Mime/Headers/AbstractHeader.php';

/**
 * A MIME Version Header for Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Headers_VersionHeader
  extends Swift_Mime_Headers_AbstractHeader
{
  
  /**
   * The version stored in this Header.
   * @var string
   * @access private
   */
  private $_version;
  
  /**
   * Creates a new VersionHeader with $name and $version.
   * @param string $name of Header
   */
  public function __construct($name)
  {
    $this->setFieldName($name);
    $this->setVersion('1.0');
  }
  
  /**
   * Set the model for the field body.
   * This method takes a string for the version number.
   * @param string $model
   */
  public function setFieldBodyModel($model)
  {
  }
  
  /**
   * Get the model for the field body.
   * This method returns a string
   * @return string
   */
  public function getFieldBodyModel()
  {
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
   * Get the string value of the body in this Header.
   * This is not necessarily RFC 2822 compliant since folding whitespace may not
   * have been added. See {@link toString()} for that.
   * @return string
   * @see toString()
   */
  public function getFieldBody()
  {
    return $this->getCachedValue();
  }
  
}
