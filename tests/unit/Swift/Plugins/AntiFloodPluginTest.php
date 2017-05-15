<?php

class Swift_Plugins_AntiFloodPluginTest extends \PHPUnit_Framework_TestCase
{
    public function testThresholdCanBeSetAndFetched()
    {
        $plugin = new Swift_Plugins_AntiFloodPlugin(10);
        $this->assertEquals(10, $plugin->getThreshold());
        $plugin->setThreshold(100);
        $this->assertEquals(100, $plugin->getThreshold());
    }

    public function testSleepTimeCanBeSetAndFetched()
    {
        $plugin = new Swift_Plugins_AntiFloodPlugin(10, 5);
        $this->assertEquals(5, $plugin->getSleepTime());
        $plugin->setSleepTime(1);
        $this->assertEquals(1, $plugin->getSleepTime());
    }

    public function testPluginStopsConnectionAfterThreshold()
    {
        $transport = $this->createTransport();
        $transport->expects($this->once())
                  ->method('start');
        $transport->expects($this->once())
                  ->method('stop');

        $evt = $this->createSendEvent($transport);

        $plugin = new Swift_Plugins_AntiFloodPlugin(10);
        for ($i = 0; $i < 12; ++$i) {
            $plugin->sendPerformed($evt);
        }
    }

    public function testPluginCanStopAndStartMultipleTimes()
    {
        $transport = $this->createTransport();
        $transport->expects($this->exactly(5))
                  ->method('start');
        $transport->expects($this->exactly(5))
                  ->method('stop');

        $evt = $this->createSendEvent($transport);

        $plugin = new Swift_Plugins_AntiFloodPlugin(2);
        for ($i = 0; $i < 11; ++$i) {
            $plugin->sendPerformed($evt);
        }
    }

    public function testPluginCanSleepDuringRestart()
    {
        $sleeper = $this->getMockBuilder('Swift_Plugins_Sleeper')->getMock();
        $sleeper->expects($this->once())
                ->method('sleep')
                ->with(10);

        $transport = $this->createTransport();
        $transport->expects($this->once())
                  ->method('start');
        $transport->expects($this->once())
                  ->method('stop');

        $evt = $this->createSendEvent($transport);

        $plugin = new Swift_Plugins_AntiFloodPlugin(99, 10, $sleeper);
        for ($i = 0; $i < 101; ++$i) {
            $plugin->sendPerformed($evt);
        }
    }

    private function createTransport()
    {
        return $this->getMockBuilder('Swift_Transport')->getMock();
    }

    private function createSendEvent($transport)
    {
        $evt = $this->getMockBuilder('Swift_Events_SendEvent')
                    ->disableOriginalConstructor()
                    ->getMock();
        $evt->expects($this->any())
            ->method('getSource')
            ->will($this->returnValue($transport));
        $evt->expects($this->any())
            ->method('getTransport')
            ->will($this->returnValue($transport));

        return $evt;
    }
}
