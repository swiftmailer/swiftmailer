<?php

Swift_DependencyContainer::getInstance()
    
  -> register('properties.charset')
  -> asValue('utf-8')
  
  -> register('mime.message')
  -> asNewInstanceOf('Swift_Mime_SimpleMessage')
  -> withDependencies(array(
    'mime.headerset',
    'mime.qpcontentencoder',
    'cache',
    'properties.charset'
  ))
  
  -> register('mime.part')
  -> asNewInstanceOf('Swift_Mime_MimePart')
  -> withDependencies(array(
    'mime.headerset',
    'mime.qpcontentencoder',
    'cache',
    'properties.charset'
  ))
  
  -> register('mime.attachment')
  -> asNewInstanceOf('Swift_Mime_Attachment')
  -> withDependencies(array(
    'mime.headerset',
    'mime.base64contentencoder',
    'cache'
  ))
  
  -> register('mime.embeddedfile')
  -> asNewInstanceOf('Swift_Mime_EmbeddedFile')
  -> withDependencies(array(
    'mime.headerset',
    'mime.base64contentencoder',
    'cache'
  ))
  
  -> register('mime.headerfactory')
  -> asNewInstanceOf('Swift_Mime_SimpleHeaderFactory')
  -> withDependencies(array(
      'mime.qpheaderencoder',
      'mime.rfc2231encoder',
      'properties.charset'
      ))
  
  -> register('mime.headerset')
  -> asNewInstanceOf('Swift_Mime_SimpleHeaderSet')
  -> withDependencies(array('mime.headerfactory', 'properties.charset'))
  
  -> register('mime.qpheaderencoder')
  -> asNewInstanceOf('Swift_Mime_HeaderEncoder_QpHeaderEncoder')
  -> withDependencies(array('mime.charstream'))
  
  -> register('mime.charstream')
  -> asNewInstanceOf('Swift_CharacterStream_ArrayCharacterStream')
  -> withDependencies(array('mime.characterreaderfactory', 'properties.charset'))
  
  -> register('mime.characterreaderfactory')
  -> asSharedInstanceOf('Swift_CharacterReaderFactory_SimpleCharacterReaderFactory')
  
  -> register('mime.qpcontentencoder')
  -> asNewInstanceOf('Swift_Mime_ContentEncoder_QpContentEncoder')
  -> addConstructorLookup('mime.charstream')
  -> addConstructorValue(true)
  
  -> register('mime.7bitcontentencoder')
  -> asNewInstanceOf('Swift_Mime_ContentEncoder_PlainContentEncoder')
  -> addConstructorValue('7bit')
  -> addConstructorValue(true)
  
  -> register('mime.8bitcontentencoder')
  -> asNewInstanceOf('Swift_Mime_ContentEncoder_PlainContentEncoder')
  -> addConstructorValue('8bit')
  -> addConstructorValue(true)
  
  -> register('mime.base64contentencoder')
  -> asSharedInstanceOf('Swift_Mime_ContentEncoder_Base64ContentEncoder')
  
  -> register('mime.rfc2231encoder')
  -> asNewInstanceOf('Swift_Encoder_Rfc2231Encoder')
  -> withDependencies(array('mime.charstream'))
  
  ;
