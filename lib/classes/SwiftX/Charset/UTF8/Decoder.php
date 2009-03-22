<?php

/* UTF-8 syntax chart
  
  0000 0000-0000 007F   0xxxxxxx
  0000 0080-0000 07FF   110xxxxx 10xxxxxx
  0000 0800-0000 FFFF   1110xxxx 10xxxxxx 10xxxxxx

  0001 0000-001F FFFF   11110xxx 10xxxxxx 10xxxxxx 10xxxxxx
  0020 0000-03FF FFFF   111110xx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
  0400 0000-7FFF FFFF   1111110x 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
*/

class SwiftX_Charset_UTF8_Decoder extends SwiftX_AbstractCharsetDecoder
{
  
  /**
   * Create a new UTF-8 Decoder.
   */
  public function __construct(SwiftX_Charset $charset)
  {
    parent::__construct($charset, 1.0, 1.0);
  }
  
  // -- Protected Methods
  
  protected function groupLoop(&$bytes, &$octetSequences)
  {
    $b = array();
    while (!$result = $this->_nextSequence($bytes, $b, $length))
    {
      $octetSequences[] = $b;
      $b = array();
    }
    
    return $result;
  }
  
  protected function decodeLoop(&$bytes, &$chars)
  {
    /* This algorithm is based on on the Java JDK, except that we don't
       generate surrogates:
       
       http://www.docjar.com/html/api/sun/nio/cs/UTF_8.java.html */
    
    $b = array();
    while (!$result = $this->_nextSequence($bytes, $b, $length))
    {
      switch ($length)
      {
        case 1:
          $chars[] = $b[1];
          break;
          
        case 2:
          $chars[] = ((($b[1] & 0x1F) << 6)  |
                      (($b[2] & 0x3F) << 0)) ;
          break;
          
        case 3:
          $chars[] = ((($b[1] & 0x0F) << 12) |
                      (($b[2] & 0x3F) << 06) |
                      (($b[3] & 0x3F) << 0)) ;
          break;
          
        case 4:
          $chars[] = ((($b[1] & 0x07) << 18)  |
                      (($b[2] & 0x3F) << 12)  |
                      (($b[3] & 0x3F) << 06)  |
                      (($b[4] & 0x3F) << 00)) ;
          break;
          
        case 5:
          $chars[] = ((($b[1] & 0x03) << 24)  |
                      (($b[2] & 0x3F) << 18)  |
                      (($b[3] & 0x3F) << 12)  |
                      (($b[4] & 0x3F) << 06)  |
                      (($b[5] & 0x3F) << 00)) ;
          break;
          
        case 6:
          $chars[] = ((($b[1] & 0x01) << 30) |
                      (($b[2] & 0x3F) << 24) |
                      (($b[3] & 0x3F) << 18) |
                      (($b[4] & 0x3F) << 12) |
                      (($b[5] & 0x3F) << 06) |
                      (($b[6] & 0x3F)))      ;
          break;
      }
      $b = array();
    }
    
    return $result;
  }
  
  // -- Private methods
  
  private function _nextSequence(&$bytes, &$b, &$length)
  {
    $b = array();
    
    //We can't use a foreach() else it will reset the array pointer
    while (list(, $b[1]) = each($bytes))
    {
      // 4 bits = 1 hex digit
      switch (($b[1] >> 4) & 0x0F)
      {
        //1 octet, 7 bits 0xxxxxxx
        case 0: case 1: case 2: case 3:
        case 4: case 5: case 6: case 7:
          $length = 1;
          return;
          
        //2 octets, 11 bits 110xxxxx 10xxxxxx
        case 12: case 13:
          if ($result = $this->_fetch(2, $bytes, $b))
          {
            return $result;
          }
          
          $length = 2;
          return;
          
        //3 octets, 16 bits 1110xxxx 10xxxxxx 10xxxxxx
        case 14:
          if ($result = $this->_fetch(3, $bytes, $b))
          {
            return $result;
          }
          
          $length = 3;
          return;
          
        //4, 5 or 6 octets (heading out of UCS-2 range, into UCS-4)
        case 15:
          
          //Now do the maths without shifting right
          switch ($b[1] & 0x0F)
          {
            //4 octets, 21 bits 1111xxxx 10xxxxxx 10xxxxxx 10xxxxxx
            case 0: case 1: case 2: case 3:
            case 4: case 5: case 6: case 7:
              if ($result = $this->_fetch(4, $bytes, $b))
              {
                return $result;
              }
              
              $length = 4;
              return;
              
            //5 octets, 26 bits 11111xxx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
            case 8: case 9: case 10: case 11:
              if ($result = $this->_fetch(5, $bytes, $b))
              {
                return $result;
              }
              
              $length = 5;
              return;
            
            //6 octets, 31 bits 11111xxx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
            case 12: case 13:
              if ($result = $this->_fetch(6, $bytes, $b))
              {
                return $result;
              }
              
              $length = 6;
              return;
            
            default:
              //prev($bytes)
              //return CoderResult::malformedForLength(1)
          }
          
          return;
        
        default:
          //prev($bytes)
          //return CoderResult::malformedForLength(1)
      }
    }
    
    return SwiftX_CoderResult::$UNDERFLOW;
  }
  
  private function _isContinuation($b)
  {
    //Checks to make sure that $b is of the form 10xx xxxx
    return (($b & 0xC0) == 0x80);
  }
  
  private function _fetch($count, &$bytes, &$b)
  {
    for ($i = 2; $i <= $count; ++$i)
    {
      list (, $b[$i]) = each($bytes);
      
      if (false === $b[$i])
      {
        return SwiftX_CoderResult::$UNDERFLOW;
      }
      
      if (!$this->_isContinuation($b[$i]))
      {
        //prev($bytes) //?
        //return CoderResult::malformedForLength($count - 1);
      }
    }
  }
  
}
