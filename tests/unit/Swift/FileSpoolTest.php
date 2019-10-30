<?php

declare(strict_types=1);

/**
 * @covers \Swift_FileSpool
 */
class FileSpoolTest extends \PHPUnit\Framework\TestCase
{
    /** @var string */
    private $path;

    protected function setUp()
    {
        $this->getName(false);
        // make sure we start with clean directory every time
        $this->path = sys_get_temp_dir().'/'.$this->getName(false).time();
        @mkdir($this->path);
    }

    protected function tearDown()
    {
        $this->cleanupDir($this->path);
    }

    private function cleanupDir($dir)
    {
        foreach (new DirectoryIterator($dir) as $file) {
            if ($file->isDot()) {
                continue;
            }
            $path = $file->getPathname();
            $file->isDir() ? $this->cleanupDir($path) : unlink($path);
        }

        rmdir($dir);
    }

    public function testRetryAttempts()
    {
        $moreAttemptsFile = $this->path.'/abc.9.message.sending';
        $afterRecover = $this->path.'/abc.10.message';
        $maxReached = $this->path.'/max.10.message.sending';
        $expired = $this->path.'/expired/max.10.message';

        file_put_contents($moreAttemptsFile, '');
        file_put_contents($maxReached, '');

        $spool = new Swift_FileSpool($this->path);
        $spool->setResendAttempts(10);

        $spool->recover(0);

        $this->assertTrue(is_file($afterRecover));
        $this->assertFalse(is_file($moreAttemptsFile));
        $this->assertFalse(is_file($maxReached));
        $this->assertTrue(is_file($expired));
    }

    public function testRecoveryTime()
    {
        $moreAttemptsFile = $this->path.'/abc.9.message.sending';
        $maxReached = $this->path.'/max.10.message.sending';

        file_put_contents($moreAttemptsFile, '');
        file_put_contents($maxReached, '');

        $spool = new Swift_FileSpool($this->path);

        $spool->recover(); // nothing should change if files have not reach the timeout

        $this->assertTrue(is_file($moreAttemptsFile));
        $this->assertTrue(is_file($maxReached));
    }

    public function testQueueMessage()
    {
        $spool = new Swift_FileSpool($this->path);

        $message = new Swift_Message('Test');
        $spool->queueMessage($message);

        $foundMessage = false;
        foreach (new DirectoryIterator($this->path) as $file) {
            if ($file->isDot()) {
                continue;
            }

            $file = $file->getRealPath();
            @unlink($file);

            if (preg_match('/\.(\d+)\.message$/i', $file, $matches)) {
                $this->assertEquals(0, $matches[1]);
                $foundMessage = true;
            }
        }

        $this->assertTrue($foundMessage);
    }
}
