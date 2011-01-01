<?php

/**
 * (c) 2010 Olaf van Zandwijk <olaf@vanzandwijk.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PostmarkTransportException thrown when an error occurs in the 
 * Postmark transport subsystem.
 *
 * @package Swift
 * @subpackage Transport
 * @author Olaf van Zandwijk
 */
class Swift_PostmarkTransportException extends Swift_TransportException
{

  /**
   * API error codes
   *
   * Whenever the Postmark server detects an input error it will
   * return an HTTP 422 status code along with a JSON object containing
   * error details: {ErrorCode: 405, Message: "details"}.
   *
   * The ErrorCode field can be used to programmatically detect
   * the type of error. Here are the supported error codes:
   *
   * 0   – Bad or missing API token
   * 300 – Invalid email request
   * 400 – Sender signature not found
   * 401 – Sender signature not confirmed
   * 402 – Invalid JSON
   * 403 – Incompatible JSON
   * 405 – Not allowed to send
   * 406 – Inactive recipient
   * 407 – Bounce not found
   * 408 – Bounce query exception
   * 409 – JSON required
   */

  /**
   * Create a new PostmarkTransportException with $message.
   *
   * @param string $message
   */
  public function __construct($message)
  {
    parent::__construct($message);
  }
  
}
