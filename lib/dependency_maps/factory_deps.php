<?php

//Dependency map
$_swiftFactoryDeps = array(
    
  //MimeFactory
  'mimefactory' => array(
    'class' => 'Swift_Di_SimpleMimeFactory',
    'args' => array(),
      'shared' => true
    ),
    
  //TransportFactory
  'transportfactory' => array(
    'class' => 'Swift_Di_SimpleTransportFactory',
    'args' => array(),
    'shared' => true
    )
  )
  ;

return $_swiftFactoryDeps;

//EOF
