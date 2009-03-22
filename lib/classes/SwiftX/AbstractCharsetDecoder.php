<?php

abstract class SwiftX_AbstractCharsetDecoder implements SwiftX_CharsetDecoder
{
  
  const DEFAULT_REPLACEMENT = 0x0000FFFD;
  
  private $_charset;
  private $_averageCharsPerByte;
  private $_maxCharsPerByte;
  private $_replacement;
  
  abstract protected function decodeLoop(&$bytes, &$chars);
  abstract protected function groupLoop(&$bytes, &$octetSequences);
  
  protected function __construct(SwiftX_Charset $charset, $averageCharsPerByte,
    $maxCharsPerByte, $replacement = self::DEFAULT_REPLACEMENT)
  {
    $this->_charset = $charset;
    $this->_averageCharsPerByte = $averageCharsPerByte;
    $this->_maxCharsPerByte = $maxCharsPerByte;
    $this->_replacement = $replacement;
  }
  
  public function getCharset()
  {
    return $this->_charset;
  }
  
  public function decode(&$bytes, &$chars)
  {
    if (!isset($chars))
    {
      $chars = (array) $chars;
    }
    
    for (;;)
    {
      $coderResult = $this->decodeLoop($bytes, $chars);
      
      if ($coderResult->isOverflow())
      {
        return $coderResult;
      }
      
      if ($coderResult->isUnderflow())
      {
        return $coderResult;
      }
    }
  }
  
  public function group(&$bytes, &$octetSequences)
  {
    if (!isset($octetSequences))
    {
      $octetSequences = (array) $octetSequences;
    }
    
    for (;;)
    {
      $coderResult = $this->groupLoop($bytes, $octetSequences);
      
      if ($coderResult->isOverflow())
      {
        return $coderResult;
      }
      
      if ($coderResult->isUnderflow())
      {
        return $coderResult;
      }
    }
  }
  
  public function reset()
  {
  }
  
}
