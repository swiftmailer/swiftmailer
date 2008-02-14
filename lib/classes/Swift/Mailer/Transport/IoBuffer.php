<?php

/*
 Input-output buffer interface from Swift Mailer.
 
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

//@require 'Swift/InputByteStream.php';
//@require 'Swift/OutputByteStream.php';

/**
 * Buffers input and output to a resource.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
interface Swift_Mailer_Transport_IoBuffer
  extends Swift_InputByteStream, Swift_OutputByteStream
{

  /**
   * Set the resource which gets written to.
   * @param resource $input
   */
  public function setInputResource($input);
  
  /**
   * Set the resource which gets read from.
   * @param resource $output
   */
  public function setOutputResource($output);
  
  /**
   * Get a line of output (including any CRLF).
   * @return string
   */
  public function readLine();
  
}
