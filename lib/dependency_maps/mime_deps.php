<?php

//Dependency map
$_swiftMimeDeps = array(
    
  //Message
  'mime.message' => array(
    'class' => 'Swift_Mime_SimpleMessage',
    'args' => array(
      array(
        'di:mime.returnpathheader',
        'di:mime.senderheader',
        'di:mime.messageidheader',
        'di:mime.dateheader',
        'di:mime.subjectheader',
        'di:mime.fromheader',
        'di:mime.replytoheader',
        'di:mime.toheader',
        'di:mime.ccheader',
        'di:mime.bccheader',
        'di:mime.mimeversionheader',
        'di:mime.contenttypeheader',
        'di:mime.contenttransferencodingheader'
        ),
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
      array(
        'di:mime.contenttypeheader',
        'di:mime.contenttransferencodingheader'
        ),
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
      array(
        'di:mime.contenttypeheader',
        'di:mime.contenttransferencodingheader',
        'di:mime.contentdispositionheader'
        ),
      'di:mime.base64contentencoder',
      'lookup:cache'
      ),
      'shared' => false
    ),
    
  //EmbeddedFile
  'mime.embeddedfile' => array(
    'class' => 'Swift_Mime_EmbeddedFile',
    'args' => array(
      array(
        'di:mime.contenttypeheader',
        'di:mime.contenttransferencodingheader',
        'di:mime.contentdispositionheader',
        'di:mime.contentidheader'
        ),
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
  
  //Return-Path
  'mime.returnpathheader' => array(
    'class' => 'Swift_Mime_Headers_PathHeader',
    'args' => array('string:Return-Path'),
    'shared' => false
    ),

  //Sender
  'mime.senderheader' => array(
    'class' => 'Swift_Mime_Headers_MailboxHeader',
    'args' => array(
      'string:Sender',
      'di:mime.qpheaderencoder'
      ),
    'shared' => false
    ),

  //Message-ID
  'mime.messageidheader' => array(
    'class' => 'Swift_Mime_Headers_IdentificationHeader',
    'args' => array('string:Message-ID'),
    'shared' => false
    ),
    
  //Content-ID
  'mime.contentidheader' => array(
    'class' => 'Swift_Mime_Headers_IdentificationHeader',
    'args' => array('string:Content-ID'),
    'shared' => false
    ),

  //Date
  'mime.dateheader' => array(
    'class' => 'Swift_Mime_Headers_DateHeader',
    'args' => array('string:Date'),
    'shared' => false
    ),

  //Subject
  'mime.subjectheader' => array(
    'class' => 'Swift_Mime_Headers_UnstructuredHeader',
    'args' => array(
      'string:Subject',
      'di:mime.qpheaderencoder'
      ),
    'shared' => false
    ),

  //From
  'mime.fromheader' => array(
    'class' => 'Swift_Mime_Headers_MailboxHeader',
    'args' => array(
      'string:From',
      'di:mime.qpheaderencoder'
      ),
    'shared' => false
    ),

  //Reply-To
  'mime.replytoheader' => array(
    'class' => 'Swift_Mime_Headers_MailboxHeader',
    'args' => array(
      'string:Reply-To',
      'di:mime.qpheaderencoder'
      ),
    'shared' => false
    ),

  //To
  'mime.toheader' => array(
    'class' => 'Swift_Mime_Headers_MailboxHeader',
    'args' => array(
      'string:To',
      'di:mime.qpheaderencoder'
      ),
    'shared' => false
    ),

  //Cc
  'mime.ccheader' => array(
    'class' => 'Swift_Mime_Headers_MailboxHeader',
    'args' => array(
      'string:Cc',
      'di:mime.qpheaderencoder'
      ),
    'shared' => false
    ),

  //Bcc
  'mime.bccheader' => array(
    'class' => 'Swift_Mime_Headers_MailboxHeader',
    'args' => array(
      'string:Bcc',
      'di:mime.qpheaderencoder'
      ),
    'shared' => false
    ),
  
  //MIME-Version
  'mime.mimeversionheader' => array(
    'class' => 'Swift_Mime_Headers_VersionHeader',
    'args' => array('string:MIME-Version'),
    'shared' => false
    ),

  //Content-Type
  'mime.contenttypeheader' => array(
    'class' => 'Swift_Mime_Headers_ParameterizedHeader',
    'args' => array(
      'string:Content-Type',
      'di:mime.qpheaderencoder'
      ),
    'shared' => false
    ),
    
  //Content-Disposition
  'mime.contentdispositionheader' => array(
    'class' => 'Swift_Mime_Headers_ParameterizedHeader',
    'args' => array(
      'string:Content-Disposition',
      'di:mime.qpheaderencoder',
      'di:mime.rfc2231encoder'
      ),
    'shared' => false
    ),
  
  //Content-Transfer-Encoding
  'mime.contenttransferencodingheader' => array(
    'class' => 'Swift_Mime_Headers_UnstructuredHeader',
    'args' => array(
      'string:Content-Transfer-Encoding',
      'di:mime.qpheaderencoder'
      ),
    'shared' => false
    ),
  
  //Custom (X-Header)
  'mime.xheader' => array(
    'class' => 'Swift_Mime_Headers_ParameterizedHeader',
    'args' => array(
      'lookup:xheadername',
      'di:mime.qpheaderencoder'
      ),
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
