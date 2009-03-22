<?php

interface SwiftX_Charset
{
  
  public function getName();
  public function getAliases();
  
  public function decode(&$bytes, &$chars);
  public function group(&$bytes, &$octetSequences);
  
  public function encode(&$chars, &$bytes);
  public function ungroup(&$octetSequences, &$bytes);
  
}
