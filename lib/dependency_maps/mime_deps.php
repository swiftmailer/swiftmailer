<?php

return array(
    
  //Message
  'message' => array(
    'class' => 'Swift_Mime_SimpleMessage',
    'args' => array(
      array(
        'di:returnpathheader',
        'di:senderheader',
        'di:messageidheader',
        'di:dateheader',
        'di:subjectheader',
        'di:fromheader',
        'di:replytoheader',
        'di:toheader',
        'di:ccheader',
        'di:bccheader',
        'di:mimeversionheader',
        'di:contenttypeheader',
        'di:contenttransferencodingheader'
        ),
      'di:qpcontentencoder'
      ),
      'shared' => false
    ),
  
  //Return-Path
  'returnpathheader' => array(
    'class' => 'Swift_Mime_Header_PathHeader',
    'args' => array('string:Return-Path'),
    'shared' => false
    ),

  //Sender
  'senderheader' => array(
    'class' => 'Swift_Mime_Header_MailboxHeader',
    'args' => array(
      'string:Sender',
      'di:qpheaderencoder'
      ),
    'shared' => false
    ),

  //Message-ID
  'messageidheader' => array(
    'class' => 'Swift_Mime_Header_IdentificationHeader',
    'args' => array('string:Message-ID'),
    'shared' => false
    ),

  //Date
  'dateheader' => array(
    'class' => 'Swift_Mime_Header_DateHeader',
    'args' => array('string:Date'),
    'shared' => false
    ),

  //Subject
  'subjectheader' => array(
    'class' => 'Swift_Mime_Header_UnstructuredHeader',
    'args' => array(
      'string:Subject',
      'di:qpheaderencoder'
      ),
    'shared' => false
    ),

  //From
  'fromheader' => array(
    'class' => 'Swift_Mime_Header_MailboxHeader',
    'args' => array(
      'string:From',
      'di:qpheaderencoder'
      ),
    'shared' => false
    ),

  //Reply-To
  'replytoheader' => array(
    'class' => 'Swift_Mime_Header_MailboxHeader',
    'args' => array(
      'string:Reply-To',
      'di:qpheaderencoder'
      ),
    'shared' => false
    ),

  //To
  'toheader' => array(
    'class' => 'Swift_Mime_Header_MailboxHeader',
    'args' => array(
      'string:To',
      'di:qpheaderencoder'
      ),
    'shared' => false
    ),

  //Cc
  'ccheader' => array(
    'class' => 'Swift_Mime_Header_MailboxHeader',
    'args' => array(
      'string:Cc',
      'di:qpheaderencoder'
      ),
    'shared' => false
    ),

  //Bcc
  'bccheader' => array(
    'class' => 'Swift_Mime_Header_MailboxHeader',
    'args' => array(
      'string:Bcc',
      'di:qpheaderencoder'
      ),
    'shared' => false
    ),
  
  //MIME-Version
  'mimeversionheader' => array(
    'class' => 'Swift_Mime_Header_VersionHeader',
    'args' => array('string:MIME-Version'),
    'shared' => false
    ),

  //Content-Type
  'contenttypeheader' => array(
    'class' => 'Swift_Mime_Header_ParameterizedHeader',
    'args' => array(
      'string:Content-Type',
      'di:qpheaderencoder',
      'di:rfc2231encoder'
      ),
    'shared' => false
    ),
  
  //Content-Transfer-Encoding
  'contenttransferencodingheader' => array(
    'class' => 'Swift_Mime_Header_ParameterizedHeader',
    'args' => array(
      'string:Content-Transfer-Encoding',
      'di:qpheaderencoder',
      'di:rfc2231encoder'
      ),
    'shared' => false
    ),
  
  //Qp Header Encoder
  'qpheaderencoder' => array(
    'class' => 'Swift_Mime_HeaderEncoder_QpHeaderEncoder',
    'args' => array('di:charstream'),
    'shared' => true
    ),
  
  //CharStream
  'charstream' => array(
    'class' => 'Swift_CharacterStream_ArrayCharacterStream',
    'args' => array(
      'di:characterreaderfactory',
      'string:utf-8'
      ),
    'shared' => false
    ),
  
  //Character Reader Factory
  'characterreaderfactory' => array(
    'class' => 'Swift_CharacterReaderFactory_SimpleCharacterReaderFactory',
    'args' => array(),
    'shared' => true
    ),
  
  //Qp content Encoder
  'qpcontentencoder' => array(
    'class' => 'Swift_Mime_ContentEncoder_QpContentEncoder',
    'args' => array('di:charstream'),
    'shared' => true
    ),
  
  //Parameter (RFC 2231) Encoder
  'rfc2231encoder' => array(
    'class' => 'Swift_Encoder_Rfc2231Encoder',
    'args' => array('di:charstream'),
    'shared' => true
    ),
  
  );
  