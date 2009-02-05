<?php

/*
 Header Signature Interface for SwiftMailer
 
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
 * Header Signer Interface used to apply Header-Based Signature to a message
 * @package Swift
 * @subpackage Signatures
 * @author Xavier De Cock <xdecock@gmail.com>
 */
interface Swift_Signers_HeaderSigner extends Swift_Signer, Swift_InputByteStream
{
  /**
   * Exclude an header from the signed headers
   *
   * @param string $header_name
   * @return Swift_Signers_HeaderSigner
   */
  public function ignoreHeader($header_name);
  /**
   * Prepare the Signer to get a new Body
   * @return Swift_Signers_HeaderSigner
   */
  public function startBody();
  /**
   * Give the signal that the body has finished streaming
   * @return Swift_Signers_HeaderSigner
   */
  public function endBody();
  /**
   * Give the headers already given
   *
   * @param Swift_Mime_SimpleHeaderSet $headers
   * @return Swift_Signers_HeaderSigner
   */
  public function setHeaders (Swift_Mime_HeaderSet $headers);
  
  /**
   * Add the header(s) to the headerSet
   *
   * @param Swift_Mime_HeaderSet $headers
   * @return Swift_Signers_HeaderSigner
   */
  public function addSignature (Swift_Mime_HeaderSet $headers);
  
  /**
   * Return the list of header a signer might tamper
   * @return array
   */
  public function getAlteredHeaders();
}
