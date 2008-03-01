<?php

//Dependency map
$_swiftTransportDeps = array(
    
  //Smtp
  'smtp' => array(
    'class' => 'Swift_Transport_EsmtpTransport',
    'args' => array(
      'di:polymorphicbuffer',
      array('di:authhandler'),
      'di:arraylog',
      'di:eventdispatcher'
      ),
      'shared' => false
    ),
    
  //IOBuffer
  'polymorphicbuffer' => array(
    'class' => 'Swift_Transport_PolymorphicBuffer',
    'args' => array(),
      'shared' => false
    ),
    
  //AUTH handler
  'authhandler' => array(
    'class' => 'Swift_Transport_Esmtp_AuthHandler',
    'args' => array(
      array(
        'di:crammd5auth',
        'di:loginauth',
        'di:plainauth'
        )
      ),
      'shared' => false
    ),
    
  //CRAM-MD5
  'crammd5auth' => array(
    'class' => 'Swift_Transport_Esmtp_Auth_CramMd5Authenticator',
    'args' => array(),
      'shared' => false
    ),
  
  //LOGIN
  'loginauth' => array(
    'class' => 'Swift_Transport_Esmtp_Auth_LoginAuthenticator',
    'args' => array(),
    'shared' => false
    ),
    
  //PLAIN
  'plainauth' => array(
    'class' => 'Swift_Transport_Esmtp_Auth_PlainAuthenticator',
    'args' => array(),
    'shared' => false
    ),
    
  //Sendmail
  'sendmail' => array(
    'class' => 'Swift_Transport_SendmailTransport',
    'args' => array(
      'di:polymorphicbuffer',
      'di:arraylog',
      'di:eventdispatcher'
      ),
    'shared' => false
    ),
    
  //Mail
  'mail' => array(
    'class' => 'Swift_Transport_MailTransport',
    'args' => array('di:arraylog'),
    'shared' => false
    ),
    
  //LoadBalanced
  'loadbalanced' => array(
    'class' => 'Swift_Transport_LoadBalancedTransport',
    'args' => array('di:arraylog'),
    'shared' => false
    ),
    
  //Failover
  'failover' => array(
    'class' => 'Swift_Transport_FailoverTransport',
    'args' => array('di:arraylog'),
    'shared' => false
    ),
    
  //ArrayLog
  'arraylog' => array(
    'class' => 'Swift_Transport_Log_ArrayLog',
    'args' => array(),
    'shared' => true
    ),
  
  //EventDispatcher
  'eventdispatcher' => array(
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
  
$_swiftTransportDeps['nativemail'] = $_swiftTransportDeps['mail'];
$_swiftTransportDeps['rotating'] = $_swiftTransportDeps['loadbalanced'];
$_swiftTransportDeps['balanced'] = $_swiftTransportDeps['loadbalanced'];
$_swiftTransportDeps['multi'] = $_swiftTransportDeps['failover'];
$_swiftTransportDeps['redundant'] = $_swiftTransportDeps['failover'];

return $_swiftTransportDeps;

//EOF
