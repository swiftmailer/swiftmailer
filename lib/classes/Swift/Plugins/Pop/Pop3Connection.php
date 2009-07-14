<?php

/*
 POP3 Connection class from Swift Mailer.
 
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
 * Pop3Connection interface for connecting and disconnecting to a POP3 host.
 * 
 * @package Swift
 * @subpackage Plugins
 * 
 * @author Chris Corbyn
 */
interface Swift_Plugins_Pop_Pop3Connection
{
  
  /**
   * Connect to the POP3 host and throw an Exception if it fails.
   * 
   * @throws Swift_Plugins_Pop_Pop3Exception
   */
  public function connect();
  
  /**
   * Disconnect from the POP3 host and throw an Exception if it fails.
   * 
   * @throws Swift_Plugins_Pop_Pop3Exception
   */
  public function disconnect();
  
}
