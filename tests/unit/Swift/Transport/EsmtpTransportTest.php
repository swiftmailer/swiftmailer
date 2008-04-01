<?php

require_once 'Swift/Transport/AbstractEsmtpTest.php';
require_once 'Swift/Transport/EsmtpTransport.php';
require_once 'Swift/Events/EventDispatcher.php';

class Swift_Transport_EsmtpTransportTest
  extends Swift_Transport_AbstractEsmtpTest
{
  
  protected function _getTransport($buf)
  {
    $context = new Mockery();
    return new Swift_Transport_EsmtpTransport(
      $buf, array(), $context->mock('Swift_Events_EventDispatcher')
      );
  }
  
}
