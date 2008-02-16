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

//@require 'Swift/Mime/Message.php';
//@require 'Swift/Mailer/Transport/IoBuffer.php';

/**
 * An ESMTP handler.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
interface Swift_Mailer_Transport_SmtpExtensionHandler
{
  
  /** Constant for continuing to process all other handlers */
  const CONTINUE_ALL = 0x001;
  
  /** Constant for continuing to process handlers which are for other keywords */
  const CONINTUE_OTHERS = 0x010;
  
  /** Constant for preventing further handlers from being invoked */
  const CONTINUE_NONE = 0x100;
  
  /**
   * Get the name of the ESMTP extension this handles.
   * @return boolean
   */
  public function getHandledKeyword();
  
  /**
   * Set the parameters which the EHLO greeting indicated.
   * @param string[] $parameters
   */
  public function setKeywordParameters(array $parameters);
  
  /**
   * Set information about the connection (e.g. encryption, username/password).
   * @param array $fields
   */
  public function setConnectionFields(array $fields);
  
  /**
   * Runs immediately after a EHLO has been issued.
   * @param Swift_Mailer_Transport_IoBuffer $buf to read/write
   * @param boolean &$continue needs to be set FALSE if the next extension shouldn't run
   */
  public function afterEhlo(Swift_Mailer_Transport_IoBuffer $buf, &$continue);
  
  /**
   * Runs when MAIL FROM is needed.
   * The $command contains the elements 'address' and 'params'.
   * This method must return $command after completion.
   * @param Swift_Mailer_Transport_IoBuffer $buf to read/write
   * @param string[] $command
   * @param boolean &$continue
   * @return string[]
   */
  public function atMailFrom(Swift_Mailer_Transport_IoBuffer $buf,
    array $command, &$continue);
  
  /**
   * Runs when RCPT TO is needed.
   * The $command contains the elements 'address' and 'params'.
   * This method must return $command after completion.
   * @param Swift_Mailer_Transport_IoBuffer $buf to read/write
   * @param string[] $command
   * @param boolean &$continue
   * @return string[]
   */
  public function atRcptTo(Swift_Mailer_Transport_IoBuffer $buf,
    array $command, &$continue);
  
  /**
   * Runs when the DATA command is due to be sent.
   * @param Swift_Mailer_Transport_IoBuffer $buf to read/write
   * @param Swift_Mime_Message $message to send
   * @param boolean &$continue
   */
  public function atData(Swift_Mailer_Transport_IoBuffer $buf,
    Swift_Mime_Message $message, &$continue);
  
}
