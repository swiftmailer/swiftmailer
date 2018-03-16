<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift;

use Swift\Mime\Attachment as BaseAttachment;
use Swift\ByteStream\FileByteStream;

/**
 * Attachment class for attaching files to a {@link \Swift\Mime\SimpleMessage}.
 *
 * @author Chris Corbyn
 */
class Attachment extends BaseAttachment
{
    /**
     * Create a new Attachment.
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
            [$this, '\\Swift\\Mime\\Attachment::__construct'],
            DependencyContainer::getInstance()
                ->createDependenciesFor('mime.attachment')
            );

        $this->setBody($data);
        $this->setFilename($filename);
        if ($contentType) {
            $this->setContentType($contentType);
        }
    }

    /**
     * Create a new Attachment from a filesystem path.
     *
     * @param string $path
     * @param string $contentType optional
     *
     * @return \Swift\Mime\Attachment
     */
    public static function fromPath($path, $contentType = null)
    {
        return (new self())->setFile(
            new FileByteStream($path),
            $contentType
        );
    }
}
