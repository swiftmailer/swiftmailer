<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Encoding to specified charset Exception class.
 * @package Swift
 * @author Shin Ohno
 */
class Swift_CharsetException extends Swift_SwiftException
{
  
  /**
   * Create a new Swift_EncoderException with $message.
   * @param string $message
   */
  public function __construct($message)
  {
    parent::__construct($message);
  }
  
}
