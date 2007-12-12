<?php

/*
 A factory for creating CharacterSetValidators.
 
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

require_once dirname(__FILE__) . '/CharacterSetValidator.php';


/**
 * A factory for creating CharacterSetValidators.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
interface Swift_CharacterSetValidatorFactory
{

  /**
   * Returns a CharacterSetValidator suitable for the charset applied.
   * @param string $charset
   * @return Swift_CharacterSetValidator
   */
  public function getValidatorFor($charset);
  
}
