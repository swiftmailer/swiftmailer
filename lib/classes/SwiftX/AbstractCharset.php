<?php

abstract class SwiftX_AbstractCharset implements SwiftX_Charset
{
  
  /** Official name of this charset */
  private $_name;
  
  /** Aliases for this charset name */
  private $_aliases;
  
  /**
   * Create a new CharsetDecoder for this Charset.
   * 
   * @return SwiftX_CharsetDecoder
   */
  abstract public function newDecoder();
  
  /**
   * Create a new CharsetEncoder for this Charset.
   * 
   * @return SwiftX_CharsetEncoder
   */
  abstract public function newEncoder();
  
  
  /** Create a new Charset */
  protected function __construct($name, array $aliases)
  {
    $this->_name = $name;
    $this->_aliases = $aliases;
  }
  
  /**
   * Returns the name of this Charset.
   * 
   * @return string
   */
  public function getName()
  {
    return $this->_name;
  }
  
  /**
   * Returns an array of aliases for this Charset.
   * 
   * @return array
   */
  public function getAliases()
  {
    return $this->_aliases;
  }
  
  /**
   * Decode the bytes provided in $bytes into characters in $chars.
   * 
   * @param array &$bytes
   * @param array &$chars
   */
  public function decode(&$bytes, &$chars)
  {
    return $this->newDecoder()->decode($bytes, $chars);
  }
  
  public function group(&$bytes, &$octetSequences)
  {
    return $this->newDecoder()->group($bytes, $octetSequences);
  }
  
  /**
   * Encode the characters provided in $chars into bytes in $bytes.
   * 
   * @param array &$chars
   * @param array &$bytes
   */
  public function encode(&$chars, &$bytes)
  {
  }
  
  public function ungroup(&$octetSequences, &$bytes)
  {
  }
  
}
