<?php

//Dependency map
$_swiftTransportDeps = array(
    
  //Smtp
  'smtp' => array(
    'class' => 'Swift_Transport_EsmtpTransport',
    'args' => array(
      'di:polymorphicbuffer',
      array('di:authhandler'),
      'di:arraylog'
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
      'di:arraylog'
      ),
    'shared' => false
    ),
    
  //ArrayLog
  'arraylog' => array(
    'class' => 'Swift_Transport_Log_ArrayLog',
    'args' => array(),
    'shared' => true
    )
  
  );

return $_swiftTransportDeps;

//EOF
