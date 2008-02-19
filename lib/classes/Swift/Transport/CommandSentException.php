<?php

/*
 Exception used by EsmtpHandler to intercept command sending Swift Mailer.
 
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


/**
 * Intercepts command sending from EsmtpHandlers.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_CommandSentException extends Exception
{
  
  /**
   * The response from the command being sent.
   * @var string
   * @access private
   */
  private $_response;
  
  /**
   * Create a new CommandSentException with $response.
   * @param string $response
   */
  public function __construct($response)
  {
    $this->_response = $response;
  }
  
  /**
   * Get the response.
   * @return string
   */
  public function getResponse()
  {
    return $this->_response;
  }
  
}
