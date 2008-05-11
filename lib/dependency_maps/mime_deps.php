<?php

//Dependency map
$_swiftMimeDeps = array(
    
  //Message
  'mime.message' => array(
    'class' => 'Swift_Mime_SimpleMessage',
    'args' => array(
      'di:mime.headerset',
      'di:mime.qpcontentencoder',
      'lookup:cache',
      'lookup:charset'
      ),
      'shared' => false
    ),
    
  //Mime Part
  'mime.part' => array(
    'class' => 'Swift_Mime_MimePart',
    'args' => array(
      'di:mime.headerset',
      'di:mime.qpcontentencoder',
      'lookup:cache',
      'lookup:charset'
      ),
      'shared' => false
    ),
    
  //Attachment
  'mime.attachment' => array(
    'class' => 'Swift_Mime_Attachment',
    'args' => array(
      'di:mime.headerset',
      'di:mime.base64contentencoder',
      'lookup:cache'
      ),
      'shared' => false
    ),
    
  //EmbeddedFile
  'mime.embeddedfile' => array(
    'class' => 'Swift_Mime_EmbeddedFile',
    'args' => array(
      'di:mime.headerset',
      'di:mime.base64contentencoder',
      'lookup:cache'
      ),
      'shared' => false
    ),
  
  //ArrayKeyCache
  'mime.arraycache' => array(
    'class' => 'Swift_KeyCache_ArrayKeyCache',
    'args' => array('di:mime.cacheinputstream'),
    'shared' => true
    ),
    
  //DiskKeyCache
  'mime.diskcache' => array(
    'class' => 'Swift_KeyCache_DiskKeyCache',
    'args' => array('di:mime.cacheinputstream', 'lookup:temppath'),
    'shared' => true
    ),
    
  //KeyCacheInputStream
  'mime.cacheinputstream' => array(
    'class' => 'Swift_KeyCache_SimpleKeyCacheInputStream',
    'args' => array(),
    'shared' => false
    ),
  
  //HeaderFactory
  'mime.headerfactory' => array(
    'class' => 'Swift_Mime_SimpleHeaderFactory',
    'args' => array('di:mime.qpheaderencoder', 'di:mime.rfc2231encoder'),
    'shared' => false
    ),
  
  //HeaderSet
  'mime.headerset' => array(
    'class' => 'Swift_Mime_SimpleHeaderSet',
    'args' => array('di:mime.headerfactory'),
    'shared' => false
    ),
  
  //Qp Header Encoder
  'mime.qpheaderencoder' => array(
    'class' => 'Swift_Mime_HeaderEncoder_QpHeaderEncoder',
    'args' => array('di:mime.charstream'),
    'shared' => true
    ),
  
  //CharStream
  'mime.charstream' => array(
    'class' => 'Swift_CharacterStream_ArrayCharacterStream',
    'args' => array(
      'di:mime.characterreaderfactory',
      'lookup:charset'
      ),
    'shared' => false
    ),
  
  //Character Reader Factory
  'mime.characterreaderfactory' => array(
    'class' => 'Swift_CharacterReaderFactory_SimpleCharacterReaderFactory',
    'args' => array(),
    'shared' => true
    ),
  
  //Qp content Encoder
  'mime.qpcontentencoder' => array(
    'class' => 'Swift_Mime_ContentEncoder_QpContentEncoder',
    'args' => array('di:mime.charstream', 'boolean:1'),
    'shared' => false
    ),
    
  //7bit content Encoder
  'mime.7bitcontentencoder' => array(
    'class' => 'Swift_Mime_ContentEncoder_PlainContentEncoder',
    'args' => array('string:7bit', 'boolean:1'),
    'shared' => true
    ),
    
  //8bit content Encoder
  'mime.8bitcontentencoder' => array(
    'class' => 'Swift_Mime_ContentEncoder_PlainContentEncoder',
    'args' => array('string:8bit', 'boolean:1'),
    'shared' => true
    ),
  
  //Base64 content Encoder
  'mime.base64contentencoder' => array(
    'class' => 'Swift_Mime_ContentEncoder_Base64ContentEncoder',
    'args' => array(),
    'shared' => true
    ),
  
  //Parameter (RFC 2231) Encoder
  'mime.rfc2231encoder' => array(
    'class' => 'Swift_Encoder_Rfc2231Encoder',
    'args' => array('di:mime.charstream'),
    'shared' => false
    ),
  
  );
  
//Aliases
$_swiftMimeDeps['mime.image'] = $_swiftMimeDeps['mime.embeddedfile'];
$_swiftMimeDeps['mime.7bitencoder'] = $_swiftMimeDeps['mime.7bitcontentencoder'];

return $_swiftMimeDeps;

//EOF
