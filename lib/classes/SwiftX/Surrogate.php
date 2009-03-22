<?php

abstract class SwiftX_Surrogate
{
  
  const MIN_HIGH = 0xD800;
  const MAX_HIGH = 0xDBFF;
  const MIN_LOW  = 0xDC00;
  const MAX_LOW  = 0xDFFF;
  const MIN = self::MIN_HIGH;
  const MAX = self::MAX_LOW;
  
  const UCS4_MIN = 0x00010000;
  const UCS4_MAX = 0x7FFFFFFF;
  
  protected function isSurrogate($uc)
  {
    return ($uc >= self::MIN && $c <= self::MAX);
  }
  
  protected function isHighSurrogate($uc)
  {
    return ($uc >= self::MIN_HIGH && $c <= self::MAX_HIGH);
  }
  
  protected function isLowSurrogate($uc)
  {
    return ($uc >= self::MIN_LOW && $uc <= self::MAX_LOW);
  }
  
  protected function high($uc)
  {
    return (0xd800 | ((($uc - self::UCS4_MIN) >> 10) & 0x3FF));
  }
  
  protected function low($uc)
  {
    return (0xDC00 | (($uc - self::UCS4_MIN) & 0x3FF));
  }
  
}
