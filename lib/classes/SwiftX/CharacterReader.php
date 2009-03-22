<?php

interface SwiftX_CharacterReader
{
  
  public function hasAvailable();
  public function getEncoding();
  public function readNext();
  public function read(&$buf = array(), $limit = 8192);
  //Get up to $limit chars as groupings of bytes (optimization for some operations)
  public function readBytes(&$buf = array(), $limit = 8192);
  public function position($offset = null); //Ouch
  public function skip($count);
  public function close();
  
}
