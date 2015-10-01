<?php


class Swift_FileByteStreamConsecutiveReadCalls extends  \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Swift_IoException
     */
    public function shouldThrowExceptionOnConsecutiveRead()
    {
        $fbs = new \Swift_ByteStream_FileByteStream('tralala');
        try {
            $fbs->read(100);
        } catch (\Swift_IoException $exc) {
            $fbs->read(100);
        }
    }
}
