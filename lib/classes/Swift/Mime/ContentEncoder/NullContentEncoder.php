<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Mime\ContentEncoder;

use Swift\Mime\ContentEncoder;
use Swift\OutputByteStream;
use Swift\InputByteStream;

/**
 * Handles the case where the email body is already encoded and you just need specify the correct
 * encoding without actually changing the encoding of the body.
 *
 * @author Jan Flora <jf@penneo.com>
 */
class NullContentEncoder implements ContentEncoder
{
    /**
     * The name of this encoding scheme (probably 7bit or 8bit).
     *
     * @var string
     */
    private $_name;

    /**
     * Creates a new NullContentEncoder with $name (probably 7bit or 8bit).
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->_name = $name;
    }

    /**
     * Encode a given string to produce an encoded string.
     *
     * @param string $string
     * @param int    $firstLineOffset ignored
     * @param int    $maxLineLength   ignored
     *
     * @return string
     */
    public function encodeString($string, $firstLineOffset = 0, $maxLineLength = 0)
    {
        return $string;
    }

    /**
     * Encode stream $in to stream $out.
     *
     * @param int $firstLineOffset ignored
     * @param int $maxLineLength   ignored
     */
    public function encodeByteStream(OutputByteStream $os, InputByteStream $is, $firstLineOffset = 0, $maxLineLength = 0)
    {
        while (false !== ($bytes = $os->read(8192))) {
            $is->write($bytes);
        }
    }

    /**
     * Get the name of this encoding scheme.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Not used.
     */
    public function charsetChanged($charset)
    {
    }
}
