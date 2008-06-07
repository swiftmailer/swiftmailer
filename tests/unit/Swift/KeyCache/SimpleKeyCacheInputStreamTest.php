<?php

require_once 'Swift/KeyCache/SimpleKeyCacheInputStream.php';
require_once 'Swift/KeyCache.php';

Mock::generate('Swift_KeyCache', 'Swift_MockKeyCache');

class Swift_KeyCache_SimpleKeyCacheInputStreamTest extends UnitTestCase
{
  
  private $_nsKey = 'ns1';
  
  public function testStreamWritesToCacheInAppendMode()
  {
    $cache = new Swift_MockKeyCache();
    $cache->expectAt(0, 'setString',
      array($this->_nsKey, 'foo', 'a', Swift_KeyCache::MODE_APPEND)
      );
    $cache->expectAt(1, 'setString',
      array($this->_nsKey, 'foo', 'b', Swift_KeyCache::MODE_APPEND)
      );
    $cache->expectAt(2, 'setString',
      array($this->_nsKey, 'foo', 'c', Swift_KeyCache::MODE_APPEND)
      );
    $cache->expectCallCount('setString', 3);
    
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
    $cache = new Swift_MockKeyCache();
    $cache->expectOnce('clearKey', array($this->_nsKey, 'foo'));
    
    $stream = new Swift_KeyCache_SimpleKeyCacheInputStream();
    $stream->setKeyCache($cache);
    $stream->setNsKey($this->_nsKey);
    $stream->setItemKey('foo');
    
    $stream->flushBuffers();
  }
  
  public function testClonedStreamStillReferencesSameCache()
  {
    $cache = new Swift_MockKeyCache();
    $cache->expectAt(0, 'setString',
      array($this->_nsKey, 'foo', 'a', Swift_KeyCache::MODE_APPEND)
      );
    $cache->expectAt(1, 'setString',
      array($this->_nsKey, 'foo', 'b', Swift_KeyCache::MODE_APPEND)
      );
    $cache->expectAt(2, 'setString',
      array('test', 'bar', 'x', Swift_KeyCache::MODE_APPEND)
      );
    $cache->expectCallCount('setString', 3);
    
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
  
}
