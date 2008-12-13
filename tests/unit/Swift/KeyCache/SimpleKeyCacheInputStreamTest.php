<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/KeyCache/SimpleKeyCacheInputStream.php';
require_once 'Swift/KeyCache.php';

class Swift_KeyCache_SimpleKeyCacheInputStreamTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_nsKey = 'ns1';
  
  public function testStreamWritesToCacheInAppendMode()
  {
    $cache = $this->_createKeyCache();
    $this->_checking(Expectations::create()
      -> one($cache)->setString($this->_nsKey, 'foo', 'a', Swift_KeyCache::MODE_APPEND)
      -> one($cache)->setString($this->_nsKey, 'foo', 'b', Swift_KeyCache::MODE_APPEND)
      -> one($cache)->setString($this->_nsKey, 'foo', 'c', Swift_KeyCache::MODE_APPEND)
      );
    
    $stream = new Swift_KeyCache_SimpleKeyCacheInputStream();
    $stream->setKeyCache($cache);
    $stream->setNsKey($this->_nsKey);
    $stream->setItemKey('foo');
    
    $stream->write('a');
    $stream->write('b');
    $stream->write('c');
  }
  
  public function testFlushContentClearsKey()
  {
    $cache = $this->_createKeyCache();
    $this->_checking(Expectations::create()
      -> one($cache)->clearKey($this->_nsKey, 'foo')
      );
    
    $stream = new Swift_KeyCache_SimpleKeyCacheInputStream();
    $stream->setKeyCache($cache);
    $stream->setNsKey($this->_nsKey);
    $stream->setItemKey('foo');
    
    $stream->flushBuffers();
  }
  
  public function testClonedStreamStillReferencesSameCache()
  {
    $cache = $this->_createKeyCache();
    $this->_checking(Expectations::create()
      -> one($cache)->setString($this->_nsKey, 'foo', 'a', Swift_KeyCache::MODE_APPEND)
      -> one($cache)->setString($this->_nsKey, 'foo', 'b', Swift_KeyCache::MODE_APPEND)
      -> one($cache)->setString('test', 'bar', 'x', Swift_KeyCache::MODE_APPEND)
      );
    
    $stream = new Swift_KeyCache_SimpleKeyCacheInputStream();
    $stream->setKeyCache($cache);
    $stream->setNsKey($this->_nsKey);
    $stream->setItemKey('foo');
    
    $stream->write('a');
    $stream->write('b');
    
    $newStream = clone $stream;
    $newStream->setKeyCache($cache);
    $newStream->setNsKey('test');
    $newStream->setItemKey('bar');
    
    $newStream->write('x');
  }
  
  // -- Creation Methods
  
  private function _createKeyCache()
  {
    return $this->_mock('Swift_KeyCache');
  }
  
}
