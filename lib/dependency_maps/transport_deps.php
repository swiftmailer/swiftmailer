<?php

Swift_DependencyContainer::getInstance()
  
  -> register('transport.smtp')
  -> asNewInstanceOf('Swift_Transport_EsmtpTransport')
  -> withDependencies(array(
    'transport.buffer',
    array('transport.authhandler'),
    'transport.eventdispatcher'
  ))
  
  -> register('transport.sendmail')
  -> asNewInstanceOf('Swift_Transport_SendmailTransport')
  -> withDependencies(array(
    'transport.buffer',
    'transport.eventdispatcher'
  ))
  
  -> register('transport.mail')
  -> asNewInstanceOf('Swift_Transport_MailTransport')
  
  -> register('transport.loadbalanced')
  -> asNewInstanceOf('Swift_Transport_LoadBalancedTransport')
  
  -> register('transport.failover')
  -> asNewInstanceOf('Swift_Transport_FailoverTransport')
  
  -> register('transport.buffer')
  -> asNewInstanceOf('Swift_Transport_StreamBuffer')
  -> withDependencies(array('transport.replacementfactory'))
  
  -> register('transport.authhandler')
  -> asNewInstanceOf('Swift_Transport_Esmtp_AuthHandler')
  -> withDependencies(array(
    array(
      'transport.crammd5auth',
      'transport.loginauth',
      'transport.plainauth'
    )
  ))
  
  -> register('transport.crammd5auth')
  -> asNewInstanceOf('Swift_Transport_Esmtp_Auth_CramMd5Authenticator')
  
  -> register('transport.loginauth')
  -> asNewInstanceOf('Swift_Transport_Esmtp_Auth_LoginAuthenticator')
  
  -> register('transport.plainauth')
  -> asNewInstanceOf('Swift_Transport_Esmtp_Auth_PlainAuthenticator')
  
  -> register('transport.eventdispatcher')
  -> asSharedInstanceOf('Swift_Events_SimpleEventDispatcher')
  -> addConstructorLookup('transport.eventtypes')
  
  -> register('transport.eventtypes')
  -> asValue(array(
      'send' => array(
        'event' => 'Swift_Events_SendEvent',
        'listener' => 'Swift_Events_SendListener'
      ),
      'transportchange' => array(
        'event' => 'Swift_Events_TransportChangeEvent',
        'listener' => 'Swift_Events_TransportChangeListener'
      ),
      'command' => array(
        'event' => 'Swift_Events_CommandEvent',
        'listener' => 'Swift_Events_CommandListener'
      ),
      'response' => array(
        'event' => 'Swift_Events_ResponseEvent',
        'listener' => 'Swift_Events_ResponseListener'
      )
  ))
  
  -> register('transport.replacementfactory')
  -> asSharedInstanceOf('Swift_StreamFilters_StringReplacementFilterFactory')
  
  ;
