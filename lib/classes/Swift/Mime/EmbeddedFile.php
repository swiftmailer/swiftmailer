<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Mime;

use Swift\KeyCache;
use Swift\IdGenerator;

/**
 * An embedded file, in a multipart message.
 *
 * @author Chris Corbyn
 */
class EmbeddedFile extends Attachment
{
    /**
     * Creates a new Attachment with $headers and $encoder.
     *
     * @param array $mimeTypes optional
     */
    public function __construct(SimpleHeaderSet $headers, ContentEncoder $encoder, KeyCache $cache, IdGenerator $idGenerator, $mimeTypes = [])
    {
        parent::__construct($headers, $encoder, $cache, $idGenerator, $mimeTypes);
        $this->setDisposition('inline');
        $this->setId($this->getId());
    }

    /**
     * Get the nesting level of this EmbeddedFile.
     *
     * Returns {@see LEVEL_RELATED}.
     *
     * @return int
     */
    public function getNestingLevel()
    {
        return self::LEVEL_RELATED;
    }
}
