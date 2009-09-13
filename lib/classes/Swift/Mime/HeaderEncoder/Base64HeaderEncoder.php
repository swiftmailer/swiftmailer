<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once dirname(__FILE__) . '/../HeaderEncoder.php';
require_once dirname(__FILE__) . '/../../Encoder/Base64Encoder.php';


/**
 * Handles Base64 (B) Header Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_HeaderEncoder_Base64HeaderEncoder
  extends Swift_Encoder_Base64Encoder
  implements Swift_Mime_HeaderEncoder
{
  
  /**
   * Get the name of this encoding scheme.
   * Returns the string 'B'.
   * @return string
   */
  public function getName()
  {
    return 'B';
  }
  
}
