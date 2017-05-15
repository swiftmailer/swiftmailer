<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An image, embedded in a multipart message.
 *
 * @author Chris Corbyn
 */
class Swift_Image extends Swift_EmbeddedFile
{
    /**
     * Create a new EmbeddedFile.
     *
     * Details may be optionally provided to the constructor.
     *
     * @param string|Swift_OutputByteStream $data
     * @param string                        $filename
     * @param string                        $contentType
     */
    public function __construct($data = null, $filename = null, $contentType = null)
    {
        parent::__construct($data, $filename, $contentType);
    }

    /**
     * Create a new Image from a filesystem path.
     *
     * @param string $path
     *
     * @return self
     */
    public static function fromPath($path)
    {
        return (new self())->setFile(new Swift_ByteStream_FileByteStream($path));
    }
}
