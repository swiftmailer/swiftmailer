<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/StreamFilters/ByteArrayReplacementFilterFactory.php';

class Swift_StreamFilters_ByteArrayReplacementFilterFactoryTest
  extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testInstancesOfByteArrayReplacementFilterAreCreated()
  {
    $factory = $this->_createFactory();
    $this->assertIsA($factory->createFilter(array(0x61), array(0x62)),
      'Swift_StreamFilters_ByteArrayReplacementFilter'
      );
  }
  
  public function testSameInstancesAreCached()
  {
    $factory = $this->_createFactory();
    $filter1 = $factory->createFilter(array(0x61), array(0x62));
    $filter2 = $factory->createFilter(array(0x61), array(0x62));
    $this->assertSame($filter1, $filter2, '%s: Instances should be cached');
  }
  
  public function testDifferingInstancesAreNotCached()
  {
    $factory = $this->_createFactory();
    $filter1 = $factory->createFilter(array(0x61), array(0x62));
    $filter2 = $factory->createFilter(array(0x61), array(0x64));
    $this->assertNotEqual($filter1, $filter2,
      '%s: Differing instances should not be cached'
      );
  }
  
  // -- Creation methods
  
  private function _createFactory()
  {
    return new Swift_StreamFilters_ByteArrayReplacementFilterFactory();
  }
  
}

