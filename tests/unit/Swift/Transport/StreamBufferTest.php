<?php

class Swift_Transport_StreamBufferTest extends \PHPUnit_Framework_TestCase
{
    public function testSettingWriteTranslationsCreatesFilters()
    {
        $factory = $this->createFactory();
        $factory->expects($this->once())
                ->method('createFilter')
                ->with('a', 'b')
                ->will($this->returnCallback(array($this, 'createFilter')));

        $buffer = $this->createBuffer($factory);
        $buffer->setWriteTranslations(array('a' => 'b'));
    }

    public function testOverridingTranslationsOnlyAddsNeededFilters()
    {
        $factory = $this->createFactory();
        $factory->expects($this->exactly(2))
                ->method('createFilter')
                ->will($this->returnCallback(array($this, 'createFilter')));

        $buffer = $this->createBuffer($factory);
        $buffer->setWriteTranslations(array('a' => 'b'));
        $buffer->setWriteTranslations(array('x' => 'y', 'a' => 'b'));
    }

    private function createBuffer($replacementFactory)
    {
        return new Swift_Transport_StreamBuffer($replacementFactory);
    }

    private function createFactory()
    {
        return $this->getMockBuilder('Swift_ReplacementFilterFactory')->getMock();
    }

    public function createFilter()
    {
        return $this->getMockBuilder('Swift_StreamFilter')->getMock();
    }
}
