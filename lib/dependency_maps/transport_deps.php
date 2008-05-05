<?php

//Dependency map
$_swiftTransportDeps = array(
    
  //Smtp
  'transport.smtp' => array(
    'class' => 'Swift_Transport_EsmtpTransport',
    'args' => array(
      'di:transport.polymorphicbuffer',
      array('di:transport.authhandler'),
      'di:transport.eventdispatcher'
      ),
      'shared' => false
    ),
    
  //IOBuffer
  'transport.polymorphicbuffer' => array(
    'class' => 'Swift_Transport_PolymorphicBuffer',
    'args' => array(),
      'shared' => false
    ),
    
  //AUTH handler
  'transport.authhandler' => array(
    'class' => 'Swift_Transport_Esmtp_AuthHandler',
    'args' => array(
      array(
        'di:transport.crammd5auth',
        'di:transport.loginauth',
        'di:transport.plainauth'
        )
      ),
      'shared' => false
    ),
    
  //CRAM-MD5
  'transport.crammd5auth' => array(
    'class' => 'Swift_Transport_Esmtp_Auth_CramMd5Authenticator',
    'args' => array(),
      'shared' => false
    ),
  
  //LOGIN
  'transport.loginauth' => array(
    'class' => 'Swift_Transport_Esmtp_Auth_LoginAuthenticator',
    'args' => array(),
    'shared' => false
    ),
    
  //PLAIN
  'transport.plainauth' => array(
    'class' => 'Swift_Transport_Esmtp_Auth_PlainAuthenticator',
    'args' => array(),
    'shared' => false
    ),
    
  //Sendmail
  'transport.sendmail' => array(
    'class' => 'Swift_Transport_SendmailTransport',
    'args' => array(
      'di:transport.polymorphicbuffer',
      'di:transport.eventdispatcher'
      ),
    'shared' => false
    ),
    
  //Mail
  'transport.mail' => array(
    'class' => 'Swift_Transport_MailTransport',
    'args' => array(),
    'shared' => false
    ),
    
  //LoadBalanced
  'transport.loadbalanced' => array(
    'class' => 'Swift_Transport_LoadBalancedTransport',
    'args' => array(),
    'shared' => false
    ),
    
  //Failover
  'transport.failover' => array(
    'class' => 'Swift_Transport_FailoverTransport',
    'args' => array(),
    'shared' => false
    ),
  
  //EventDispatcher
  'transport.eventdispatcher' => array(
    'class' => 'Swift_Events_SimpleEventDispatcher',
    'args' => array(array(
      'send' => array(
        'event' => 'string:Swift_Events_SendEvent',
        'listener' => 'string:Swift_Events_SendListener'
        ),
      'transportchange' => array(
        'event' => 'string:Swift_Events_TransportChangeEvent',
        'listener' => 'string:Swift_Events_TransportChangeListener'
        ),
      'command' => array(
        'event' => 'string:Swift_Events_CommandEvent',
        'listener' => 'string:Swift_Events_CommandListener'
        ),
      'response' => array(
        'event' => 'string:Swift_Events_ResponseEvent',
        'listener' => 'string:Swift_Events_ResponseListener'
        )
      )),
    'shared' => true
    )
  
  );
  
$_swiftTransportDeps['transport.nativemail'] = $_swiftTransportDeps['transport.mail'];
$_swiftTransportDeps['transport.rotating'] = $_swiftTransportDeps['transport.loadbalanced'];
$_swiftTransportDeps['transport.balanced'] = $_swiftTransportDeps['transport.loadbalanced'];
$_swiftTransportDeps['transport.multi'] = $_swiftTransportDeps['transport.failover'];
$_swiftTransportDeps['transport.redundant'] = $_swiftTransportDeps['transport.failover'];

return $_swiftTransportDeps;

//EOF
