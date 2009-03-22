<?php

class SwiftX_Charset_UTF8 extends SwiftX_AbstractCharset
{
  
  /**
   * Create a new UTF-8 Charset.
   */
  public function __construct()
  {
    parent::__construct('UTF-8', array());
  }
  
  /**
   * Create a new instance of the CharsetDecoder for UTF-8.
   * 
   * @return SwiftX_CharsetDecoder
   */
  public function newDecoder()
  {
    return new SwiftX_Charset_UTF8_Decoder($this);
  }
  
  public function newEncoder()
  {
  }
  
}
