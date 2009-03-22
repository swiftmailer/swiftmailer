<?php

class SwiftX_Surrogate_Generator extends SwiftX_Surrogate
{
  
  //Not going to generate surrogates in UTF-8, but we will parse them
  public function generate($uc, $length, &$chars)
  {
    if ($uc <= 0xFFFF)
    {
      if ($this->isSurrogate($uc))
      {
        //error and then...
        return -1;
      }
      
      $chars[] = $uc;
      return 1;
    }
    
    if ($uc < self::UCS4_MIN)
    {
      //error and then...
      return -1;
    }
    
    if ($uc <= self::UCS4_MAX)
    {
      $chars[] = $this->high($uc);
      $chars[] = $this->low($uc);
    }
  }
  
}
