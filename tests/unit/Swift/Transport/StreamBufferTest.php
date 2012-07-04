<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/StreamBuffer.php';
require_once 'Swift/ReplacementFilterFactory.php';
require_once 'Swift/StreamFilter.php';

class Swift_Transport_StreamBufferTest extends Swift_Tests_SwiftUnitTestCase
{
    public function testSettingWriteTranslationsCreatesFilters()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> one($factory)->createFilter('a', 'b') -> returns($this->_createFilter())
            -> never($factory)
            );
        $buffer = $this->_createBuffer($factory);
        $buffer->setWriteTranslations(array('a' => 'b'));
    }

    public function testOverridingTranslationsOnlyAddsNeededFilters()
    {
        $factory = $this->_createFactory();
        $this->_checking(Expectations::create()
            -> one($factory)->createFilter('a', 'b') -> returns($this->_createFilter())
            -> one($factory)->createFilter('x', 'y') -> returns($this->_createFilter())
            -> never($factory)
            );
        $buffer = $this->_createBuffer($factory);
        $buffer->setWriteTranslations(array('a' => 'b'));
        $buffer->setWriteTranslations(array('x' => 'y', 'a' => 'b'));
    }

    // -- Creation methods

    private function _createBuffer($replacementFactory)
    {
        return new Swift_Transport_StreamBuffer($replacementFactory);
    }

    private function _createFactory()
    {
        return $this->_mock('Swift_ReplacementFilterFactory');
    }

    private function _createFilter()
    {
        return $this->_stub('Swift_StreamFilter');
    }
}
