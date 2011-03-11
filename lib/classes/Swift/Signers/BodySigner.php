<?php

/*
 Body Signature Interface for SwiftMailer
 
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
 * Body Signer Interface used to apply Body-Based Signature to a message
 * @package Swift
 * @subpackage Signatures
 * @author Xavier De Cock <xdecock@gmail.com>
 */
Interface Swift_Signers_BodySigner extends Swift_Signer
{
  /**
   * Add the header(s) to the headerSet
   *
   * @param Swift_Mime_HeaderSet $headers
   * @return Swift_Signers_HeaderSigner
   */
  public function SignMessage (Swift_Message $message);
  
  /**
   * Return the list of header a signer might tamper
   * @return array
   */
  public function getAlteredHeaders();
}