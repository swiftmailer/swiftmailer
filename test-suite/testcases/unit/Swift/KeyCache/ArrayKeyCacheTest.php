<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/InputByteStream.php';
require_once 'Swift/OutputByteStream.php';
require_once 'Swift/KeyCache/ArrayKeyCache.php';
require_once 'Swift/KeyCache/KeyCacheInputStream.php';
require_once 'Swift/KeyCache.php';

Mock::generate('Swift_InputByteStream', 'Swift_MockInputByteStream');
Mock::generate('Swift_OutputByteStream', 'Swift_MockOutputByteStream');
Mock::generate('Swift_KeyCache_KeyCacheInputStream',
  'Swift_KeyCache_MockKeyCacheInputStream'
  );

class Swift_KeyCache_ArrayKeyCacheTest extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_cache;
  private $_inputStream;
  private $_key1 = 'key1';
  private $_key2 = 'key2';
  
  public function setUp()
  {
    $this->_inputStream = new Swift_KeyCache_MockKeyCacheInputStream();
    $this->_cache = new Swift_KeyCache_ArrayKeyCache($this->_inputStream);
  }
  
  public function testStringDataCanBeSetAndFetched()
  {
    $this->_cache->setString(
      $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE
      );
    $this->assertEqual('test', $this->_cache->getString($this->_key1, 'foo'));
  }
  
  public function testStringDataCanBeOverwritten()
  {
    $this->_cache->setString(
      $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE
      );
    $this->_cache->setString(
      $this->_key1, 'foo', 'whatever', Swift_KeyCache::MODE_WRITE
      );
    $this->assertEqual('whatever', $this->_cache->getString($this->_key1, 'foo'));
  }
  
  public function testStringDataCanBeAppended()
  {
    $this->_cache->setString(
      $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE
      );
    $this->_cache->setString(
      $this->_key1, 'foo', 'ing', Swift_KeyCache::MODE_APPEND
      );
    $this->assertEqual('testing', $this->_cache->getString($this->_key1, 'foo'));
  }
  
  public function testHasKeyReturnValue()
  {
    $this->assertFalse($this->_cache->hasKey($this->_key1, 'foo'));
    $this->_cache->setString(
      $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE
      );
    $this->assertTrue($this->_cache->hasKey($this->_key1, 'foo'));
  }
  
  public function testNsKeyIsWellPartitioned()
  {
    $this->_cache->setString(
      $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE
      );
    $this->_cache->setString(
      $this->_key2, 'foo', 'ing', Swift_KeyCache::MODE_WRITE
      );
    $this->assertEqual('test', $this->_cache->getString($this->_key1, 'foo'));
    $this->assertEqual('ing', $this->_cache->getString($this->_key2, 'foo'));
  }
  
  public function testItemKeyIsWellPartitioned()
  {
    $this->_cache->setString(
      $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE
      );
    $this->_cache->setString(
      $this->_key1, 'bar', 'ing', Swift_KeyCache::MODE_WRITE
      );
    $this->assertEqual('test', $this->_cache->getString($this->_key1, 'foo'));
    $this->assertEqual('ing', $this->_cache->getString($this->_key1, 'bar'));
  }
  
  public function testByteStreamCanBeImported()
  {
    $os = new Swift_MockOutputByteStream();
    $os->setReturnValueAt(0, 'read', 'abc');
    $os->setReturnValueAt(1, 'read', 'def');
    $os->setReturnValueAt(2, 'read', false);
    
    $this->_cache->importFromByteStream(
      $this->_key1, 'foo', $os, Swift_KeyCache::MODE_WRITE
      );
    $this->assertEqual('abcdef', $this->_cache->getString($this->_key1, 'foo'));
  }
  
  public function testByteStreamCanBeAppended()
  {
    $os1 = new Swift_MockOutputByteStream();
    $os1->setReturnValueAt(0, 'read', 'abc');
    $os1->setReturnValueAt(1, 'read', 'def');
    $os1->setReturnValueAt(2, 'read', false);
    
    $os2 = new Swift_MockOutputByteStream();
    $os2->setReturnValueAt(0, 'read', 'xyz');
    $os2->setReturnValueAt(1, 'read', 'uvw');
    $os2->setReturnValueAt(2, 'read', false);
    
    $this->_cache->importFromByteStream(
      $this->_key1, 'foo', $os1, Swift_KeyCache::MODE_APPEND
      );
    $this->_cache->importFromByteStream(
      $this->_key1, 'foo', $os2, Swift_KeyCache::MODE_APPEND
      );
    
    $this->assertEqual('abcdefxyzuvw', $this->_cache->getString($this->_key1, 'foo'));
  }
  
  public function testByteStreamAndStringCanBeAppended()
  {
    $this->_cache->setString(
      $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_APPEND
      );
    
    $os = new Swift_MockOutputByteStream();
    $os->setReturnValueAt(0, 'read', 'abc');
    $os->setReturnValueAt(1, 'read', 'def');
    $os->setReturnValueAt(2, 'read', false);
    
    $this->_cache->importFromByteStream(
      $this->_key1, 'foo', $os, Swift_KeyCache::MODE_APPEND
      );
    $this->assertEqual('testabcdef', $this->_cache->getString($this->_key1, 'foo'));
  }
  
  public function testDataCanBeExportedToByteStream()
  {
    //See acceptance test for more detail
    
    $this->_cache->setString(
      $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE
      );
    
    $is = new Swift_MockInputByteStream();
    $is->expectAtLeastOnce('write', array('*'));
    
    $this->_cache->exportToByteStream($this->_key1, 'foo', $is);
  }
  
  public function testKeyCanBeCleared()
  {
    $this->_cache->setString(
      $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE
      );
    $this->assertTrue($this->_cache->hasKey($this->_key1, 'foo'));
    $this->_cache->clearKey($this->_key1, 'foo');
    $this->assertFalse($this->_cache->hasKey($this->_key1, 'foo'));
  }
  
  public function testNsKeyCanBeCleared()
  {
    $this->_cache->setString(
      $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE
      );
    $this->_cache->setString(
      $this->_key1, 'bar', 'xyz', Swift_KeyCache::MODE_WRITE
      );
    $this->assertTrue($this->_cache->hasKey($this->_key1, 'foo'));
    $this->assertTrue($this->_cache->hasKey($this->_key1, 'bar'));
    $this->_cache->clearAll($this->_key1);
    $this->assertFalse($this->_cache->hasKey($this->_key1, 'foo'));
    $this->assertFalse($this->_cache->hasKey($this->_key1, 'bar'));
  }
  
}
