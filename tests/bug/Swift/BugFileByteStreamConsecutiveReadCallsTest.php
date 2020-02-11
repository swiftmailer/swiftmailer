<?php

class Swift_FileByteStreamConsecutiveReadCalls extends \PHPUnit\Framework\TestCase
{
    public function testShouldThrowExceptionOnConsecutiveRead()
    {
        $this->expectException(\Swift_IoException::class);

        $fbs = new \Swift_ByteStream_FileByteStream('does not exist');
        try {
            $fbs->read(100);
        } catch (\Swift_IoException $exc) {
            $fbs->read(100);
        }
    }
}
