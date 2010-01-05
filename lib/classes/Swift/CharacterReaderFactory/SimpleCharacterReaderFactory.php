<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Standard factory for creating CharacterReaders.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
class Swift_CharacterReaderFactory_SimpleCharacterReaderFactory
  implements Swift_CharacterReaderFactory
{

  /**
   * A map of charset patterns to their implementation classes.
   * @var array
   * @access private
   */
  private $_map = array();
  
  /**
   * Factories which have already been loaded.
   * @var Swift_CharacterReaderFactory[]
   * @access private
   */
  private $_loaded = array();
  
  /**
   * Creates a new CharacterReaderFactory.
   */
  public function __construct()
  {
    $prefix = 'Swift_CharacterReader_';
    
    $singleByte = array(
      'class' => $prefix . 'GenericFixedWidthReader',
      'constructor' => array(1)
      );
    
    $doubleByte = array(
      'class' => $prefix . 'GenericFixedWidthReader',
      'constructor' => array(2)
      );
      
    $fourBytes = array(
      'class' => $prefix . 'GenericFixedWidthReader',
      'constructor' => array(4)
      );
    
    //Utf-8
    $this->_map['utf-?8'] = array(
      'class' => $prefix . 'Utf8Reader',
      'constructor' => array()
      );
    
    //7-8 bit charsets
    $this->_map['(us-)?ascii'] = $singleByte;
    $this->_map['(iso|iec)-?8859-?[0-9]+'] = $singleByte;
    $this->_map['windows-?125[0-9]'] = $singleByte;
    $this->_map['cp-?[0-9]+'] = $singleByte;
    $this->_map['ansi'] = $singleByte;
    $this->_map['macintosh'] = $singleByte;
    $this->_map['koi-?7'] = $singleByte;
    $this->_map['koi-?8-?.+'] = $singleByte;
    $this->_map['mik'] = $singleByte;
    $this->_map['(cork|t1)'] = $singleByte;
    $this->_map['v?iscii'] = $singleByte;
    
    //16 bits
    $this->_map['(ucs-?2|utf-?16)'] = $doubleByte;
    
    //32 bits
    $this->_map['(ucs-?4|utf-?32)'] = $fourBytes;
    
    //Fallback
    $this->_map['.*'] = $singleByte;
  }
  
  /**
   * Returns a CharacterReader suitable for the charset applied.
   * @param string $charset
   * @return Swift_CharacterReader
   */
  public function getReaderFor($charset)
  {
    $charset = trim(strtolower($charset));
    foreach ($this->_map as $pattern => $spec)
    {
      $re = '/^' . $pattern . '$/D';
      if (preg_match($re, $charset))
      {
        if (!array_key_exists($pattern, $this->_loaded))
        {
          $reflector = new ReflectionClass($spec['class']);
          if ($reflector->getConstructor())
          {
            $reader = $reflector->newInstanceArgs($spec['constructor']);
          }
          else
          {
            $reader = $reflector->newInstance();
          }
          $this->_loaded[$pattern] = $reader;
        }
        return $this->_loaded[$pattern];
      }
    }
  }
  
}
