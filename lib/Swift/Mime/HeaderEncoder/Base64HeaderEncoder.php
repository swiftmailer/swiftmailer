<?php

/*
 The Base64 header encoder in Swift Mailer.
 
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

require_once dirname(__FILE__) . '/../HeaderEncoder.php';
require_once dirname(__FILE__) . '/../../Encoder/Base64Encoder.php';


/**
 * Handles Base64 (B) Header Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_HeaderEncoder_Base64HeaderEncoder
  extends Swift_Encoder_Base64Encoder
  implements Swift_Mime_HeaderEncoder
{
  
  /**
   * Get the name of this encoding scheme.
   * Returns the string 'B'.
   * @return string
   */
  public function getName()
  {
    return 'B';
  }
  
}
