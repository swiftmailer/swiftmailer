<?php

require_once 'Swift/Transport/EsmtpTransport/EventSupportTest.php';
require_once 'Swift/Transport/SendmailTransport.php';
require_once 'Swift/Events/EventDispatcher.php';
require_once 'Swift/Events/EventObject.php';

class Swift_Transport_SendmailTransport_EventSupportTest
  extends Swift_Transport_EsmtpTransport_EventSupportTest
{
  
  protected function _getTransport($buf, $dispatcher = null, $command = '/usr/sbin/sendmail -bs')
  {
    if (!$dispatcher)
    {
      $context = new Mockery();
      $dispatcher = $context->mock('Swift_Events_EventDispatcher');
    }
    $transport = new Swift_Transport_SendmailTransport($buf, $dispatcher);
    $transport->setCommand($command);
    return $transport;
  }
  
}
