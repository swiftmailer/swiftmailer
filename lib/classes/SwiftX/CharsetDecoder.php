<?php

interface SwiftX_CharsetDecoder
{
  
  public function getCharset();
  
  public function decode(&$bytes, &$chars);
  
  public function group(&$bytes, &$octetSequences);
  
  public function reset();
  
}
