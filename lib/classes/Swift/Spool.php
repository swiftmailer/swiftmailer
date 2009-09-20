<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier <fabien.potencier@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Interface for spools.
 * @package Swift
 * @author  Fabien Potencier
 */
interface Swift_Spool
{
  public function start();
  public function stop();
  public function isStarted();
  public function queueMessage(Swift_Mime_Message $message);
  public function flushQueue(Swift_Transport $transport);
}
