<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift;

use Swift\Mime\MimePart as BaseMimePart;

/**
 * A MIME part, in a multipart message.
 *
 * @author Chris Corbyn
 */
class MimePart extends BaseMimePart
{
    /**
     * Create a new MimePart.
     *
     * Details may be optionally passed into the constructor.
     *
     * @param string $body
     * @param string $contentType
     * @param string $charset
     */
    public function __construct($body = null, $contentType = null, $charset = null)
    {
        call_user_func_array(
            [$this, '\\Swift\\Mime\\MimePart::__construct'],
            DependencyContainer::getInstance()
                ->createDependenciesFor('mime.part')
            );

        if (!isset($charset)) {
            $charset = DependencyContainer::getInstance()
                ->lookup('properties.charset');
        }
        $this->setBody($body);
        $this->setCharset($charset);
        if ($contentType) {
            $this->setContentType($contentType);
        }
    }
}
