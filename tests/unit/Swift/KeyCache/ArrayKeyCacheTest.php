<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/InputByteStream.php';
require_once 'Swift/OutputByteStream.php';
require_once 'Swift/KeyCache/ArrayKeyCache.php';
require_once 'Swift/KeyCache/KeyCacheInputStream.php';
require_once 'Swift/KeyCache.php';

class Swift_KeyCache_ArrayKeyCacheTest extends Swift_Tests_SwiftUnitTestCase
{
    private $_key1 = 'key1';
    private $_key2 = 'key2';

    public function testStringDataCanBeSetAndFetched()
    {
        $is = $this->_createKeyCacheInputStream(true);
        $cache = $this->_createCache($is);
        $cache->setString(
            $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE
            );
        $this->assertEqual('test', $cache->getString($this->_key1, 'foo'));
    }

    public function testStringDataCanBeOverwritten()
    {
        $is = $this->_createKeyCacheInputStream(true);
        $cache = $this->_createCache($is);
        $cache->setString(
            $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE
            );
        $cache->setString(
            $this->_key1, 'foo', 'whatever', Swift_KeyCache::MODE_WRITE
            );

        $this->assertEqual('whatever', $cache->getString($this->_key1, 'foo'));
    }

    public function testStringDataCanBeAppended()
    {
        $is = $this->_createKeyCacheInputStream(true);
        $cache = $this->_createCache($is);
        $cache->setString(
            $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE
            );
        $cache->setString(
            $this->_key1, 'foo', 'ing', Swift_KeyCache::MODE_APPEND
            );

        $this->assertEqual('testing', $cache->getString($this->_key1, 'foo'));
    }

    public function testHasKeyReturnValue()
    {
        $is = $this->_createKeyCacheInputStream(true);
        $cache = $this->_createCache($is);
        $cache->setString(
            $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE
            );

        $this->assertTrue($cache->hasKey($this->_key1, 'foo'));
    }

    public function testNsKeyIsWellPartitioned()
    {
        $is = $this->_createKeyCacheInputStream(true);
        $cache = $this->_createCache($is);
        $cache->setString(
            $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE
            );
        $cache->setString(
            $this->_key2, 'foo', 'ing', Swift_KeyCache::MODE_WRITE
            );

        $this->assertEqual('test', $cache->getString($this->_key1, 'foo'));
        $this->assertEqual('ing', $cache->getString($this->_key2, 'foo'));
    }

    public function testItemKeyIsWellPartitioned()
    {
        $is = $this->_createKeyCacheInputStream(true);
        $cache = $this->_createCache($is);
        $cache->setString(
            $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE
            );
        $cache->setString(
            $this->_key1, 'bar', 'ing', Swift_KeyCache::MODE_WRITE
            );

        $this->assertEqual('test', $cache->getString($this->_key1, 'foo'));
        $this->assertEqual('ing', $cache->getString($this->_key1, 'bar'));
    }

    public function testByteStreamCanBeImported()
    {
        $os = $this->_createOutputStream();
        $this->_checking(Expectations::create()
            -> one($os)->read(optional()) -> returns('abc')
            -> one($os)->read(optional()) -> returns('def')
            -> one($os)->read(optional()) -> returns(false)
            -> ignoring($os)
            );
        $is = $this->_createKeyCacheInputStream(true);
        $cache = $this->_createCache($is);
        $cache->importFromByteStream(
            $this->_key1, 'foo', $os, Swift_KeyCache::MODE_WRITE
            );
        $this->assertEqual('abcdef', $cache->getString($this->_key1, 'foo'));
    }

    public function testByteStreamCanBeAppended()
    {
        $os1 = $this->_createOutputStream();
        $os2 = $this->_createOutputStream();
        $this->_checking(Expectations::create()
            -> one($os1)->read(optional()) -> returns('abc')
            -> one($os1)->read(optional()) -> returns('def')
            -> one($os1)->read(optional()) -> returns(false)
            -> ignoring($os1)

            -> one($os2)->read(optional()) -> returns('xyz')
            -> one($os2)->read(optional()) -> returns('uvw')
            -> one($os2)->read(optional()) -> returns(false)
            -> ignoring($os2)
            );
        $is = $this->_createKeyCacheInputStream(true);

        $cache = $this->_createCache($is);

        $cache->importFromByteStream(
            $this->_key1, 'foo', $os1, Swift_KeyCache::MODE_APPEND
            );
        $cache->importFromByteStream(
            $this->_key1, 'foo', $os2, Swift_KeyCache::MODE_APPEND
            );

        $this->assertEqual('abcdefxyzuvw', $cache->getString($this->_key1, 'foo'));
    }

    public function testByteStreamAndStringCanBeAppended()
    {
        $os = $this->_createOutputStream();
        $this->_checking(Expectations::create()
            -> one($os)->read(optional()) -> returns('abc')
            -> one($os)->read(optional()) -> returns('def')
            -> one($os)->read(optional()) -> returns(false)
            -> ignoring($os)
            );
        $is = $this->_createKeyCacheInputStream(true);

        $cache = $this->_createCache($is);

        $cache->setString(
            $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_APPEND
            );
        $cache->importFromByteStream(
            $this->_key1, 'foo', $os, Swift_KeyCache::MODE_APPEND
            );
        $this->assertEqual('testabcdef', $cache->getString($this->_key1, 'foo'));
    }

    public function testDataCanBeExportedToByteStream()
    {
        //See acceptance test for more detail
        $is = $this->_createInputStream();
        $this->_checking(Expectations::create()
            -> atLeast(1)->of($is)->write(any())
            -> ignoring($is)
            );
        $kcis = $this->_createKeyCacheInputStream(true);

        $cache = $this->_createCache($kcis);

        $cache->setString(
            $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE
            );

        $cache->exportToByteStream($this->_key1, 'foo', $is);
    }

    public function testKeyCanBeCleared()
    {
        $is = $this->_createKeyCacheInputStream(true);
        $cache = $this->_createCache($is);

        $cache->setString(
            $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE
            );
        $this->assertTrue($cache->hasKey($this->_key1, 'foo'));
        $cache->clearKey($this->_key1, 'foo');
        $this->assertFalse($cache->hasKey($this->_key1, 'foo'));
    }

    public function testNsKeyCanBeCleared()
    {
        $is = $this->_createKeyCacheInputStream(true);
        $cache = $this->_createCache($is);

        $cache->setString(
            $this->_key1, 'foo', 'test', Swift_KeyCache::MODE_WRITE
            );
        $cache->setString(
            $this->_key1, 'bar', 'xyz', Swift_KeyCache::MODE_WRITE
            );
        $this->assertTrue($cache->hasKey($this->_key1, 'foo'));
        $this->assertTrue($cache->hasKey($this->_key1, 'bar'));
        $cache->clearAll($this->_key1);
        $this->assertFalse($cache->hasKey($this->_key1, 'foo'));
        $this->assertFalse($cache->hasKey($this->_key1, 'bar'));
    }

    // -- Creation methods

    private function _createCache($is)
    {
        return new Swift_KeyCache_ArrayKeyCache($is);
    }

    private function _createKeyCacheInputStream($stub = false)
    {
        return $stub
            ? $this->_stub('Swift_KeyCache_KeyCacheInputStream')
            : $this->_mock('Swift_KeyCache_KeyCacheInputStream')
            ;
    }

    private function _createOutputStream($stub = false)
    {
        return $stub
            ? $this->_stub('Swift_OutputByteStream')
            : $this->_mock('Swift_OutputByteStream')
            ;
    }

    private function _createInputStream($stub = false)
    {
        return $stub
            ? $this->_stub('Swift_InputByteStream')
            : $this->_mock('Swift_InputByteStream')
            ;
    }
}
