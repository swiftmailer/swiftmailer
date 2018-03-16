<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift;

use Swift\Mime\EmbeddedFile as BaseEmbeddedFile;
use Swift\ByteStream\FileByteStream;

/**
 * An embedded file, in a multipart message.
 *
 * @author Chris Corbyn
 */
class EmbeddedFile extends BaseEmbeddedFile
{
    /**
     * Create a new EmbeddedFile.
     *
     * Details may be optionally provided to the constructor.
     *
     * @param string|\Swift\OutputByteStream $data
     * @param string                        $filename
     * @param string                        $contentType
     */
    public function __construct($data = null, $filename = null, $contentType = null)
    {
        call_user_func_array(
            [$this, '\\Swift\\Mime\\EmbeddedFile::__construct'],
            DependencyContainer::getInstance()
                ->createDependenciesFor('mime.embeddedfile')
            );

        $this->setBody($data);
        $this->setFilename($filename);
        if ($contentType) {
            $this->setContentType($contentType);
        }
    }

    /**
     * Create a new EmbeddedFile from a filesystem path.
     *
     * @param string $path
     *
     * @return \Swift\Mime\EmbeddedFile
     */
    public static function fromPath($path)
    {
        return (new self())->setFile(new FileByteStream($path));
    }
}
