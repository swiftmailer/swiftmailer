<?php

/*
 Encodings for Swift Mailer.
 
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

//@require 'Swift/DependencyContainer.php';

/**
 * Provides quick access to each encoding type.
 * 
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
class Swift_Encoding
{
  
  /**
   * Get the Encoder that provides 7-bit encoding.
   * 
   * @return Swift_Mime_ContentEncoder
   */
  public static function get7BitEncoding()
  {
    return self::_lookup('mime.7bitcontentencoder');
  }
  
  /**
   * Get the Encoder that provides 8-bit encoding.
   * 
   * @return Swift_Mime_ContentEncoder
   */
  public static function get8BitEncoding()
  {
    return self::_lookup('mime.8bitcontentencoder');
  }
  
  /**
   * Get the Encoder that provides Quoted-Printable (QP) encoding.
   * 
   * @return Swift_Mime_ContentEncoder
   */
  public static function getQpEncoding()
  {
    return self::_lookup('mime.qpcontentencoder');
  }
  
  /**
   * Get the Encoder that provides Base64 encoding.
   * 
   * @return Swift_Mime_ContentEncoder
   */
  public static function getBase64Encoding()
  {
    return self::_lookup('mime.base64contentencoder');
  }
  
  // -- Private Static Methods
  
  private static function _lookup($key)
  {
    return Swift_DependencyContainer::getInstance()->lookup($key);
  }
  
}
