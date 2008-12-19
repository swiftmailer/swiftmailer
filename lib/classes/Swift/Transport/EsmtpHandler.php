<?php

/*
 The interface an ESMTP extension handler implement in Swift Mailer.
 
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

//@require 'Swift/Transport/EsmtpBufferWrapper.php';

/**
 * An ESMTP handler.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
interface Swift_Transport_EsmtpHandler
{
  
  /**
   * Get the name of the ESMTP extension this handles.
   * @return boolean
   */
  public function getHandledKeyword();
  
  /**
   * Set the parameters which the EHLO greeting indicated.
   * @param string[] $parameters
   */
  public function setKeywordParams(array $parameters);
  
  /**
   * Runs immediately after a EHLO has been issued.
   * @param Swift_Transport_SmtpAgent $agent to read/write
   */
  public function afterEhlo(Swift_Transport_SmtpAgent $agent);
  
  /**
   * Get params which are appended to MAIL FROM:<>.
   * @return string[]
   */
  public function getMailParams();
  
  /**
   * Get params which are appended to RCPT TO:<>.
   * @return string[]
   */
  public function getRcptParams();
  
  /**
   * Runs when a command is due to be sent.
   * @param Swift_Transport_SmtpAgent $agent to read/write
   * @param string $command to send
   * @param int[] $codes expected in response
   * @param string[] &$failedRecipients
   * @param boolean &$stop to be set true if the command is now sent
   */
  public function onCommand(Swift_Transport_SmtpAgent $agent,
    $command, $codes = array(), &$failedRecipients = null, &$stop = false);
    
  /**
   * Returns +1, -1 or 0 according to the rules for usort().
   * This method is called to ensure extensions can be execute in an appropriate order.
   * @param string $esmtpKeyword to compare with
   * @return int
   */
  public function getPriorityOver($esmtpKeyword);
  
  /**
   * Returns an array of method names which are exposed to the Esmtp class.
   * @return string[]
   */
  public function exposeMixinMethods();
  
  /**
   * Tells this handler to clear any buffers and reset its state.
   */
  public function resetState();
  
}
