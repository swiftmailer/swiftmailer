<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/StreamFilters/StringReplacementFilterFactory.php';

class Swift_StreamFilters_StringReplacementFilterFactoryTest
    extends Swift_Tests_SwiftUnitTestCase
{
    public function testInstancesOfStringReplacementFilterAreCreated()
    {
        $factory = $this->_createFactory();
        $this->assertIsA($factory->createFilter('a', 'b'),
            'Swift_StreamFilters_StringReplacementFilter'
            );
    }

    public function testSameInstancesAreCached()
    {
        $factory = $this->_createFactory();
        $filter1 = $factory->createFilter('a', 'b');
        $filter2 = $factory->createFilter('a', 'b');
        $this->assertSame($filter1, $filter2, '%s: Instances should be cached');
    }

    public function testDifferingInstancesAreNotCached()
    {
        $factory = $this->_createFactory();
        $filter1 = $factory->createFilter('a', 'b');
        $filter2 = $factory->createFilter('a', 'c');
        $this->assertNotEqual($filter1, $filter2,
            '%s: Differing instances should not be cached'
            );
    }

    // -- Creation methods

    private function _createFactory()
    {
        return new Swift_StreamFilters_StringReplacementFilterFactory();
    }
}
