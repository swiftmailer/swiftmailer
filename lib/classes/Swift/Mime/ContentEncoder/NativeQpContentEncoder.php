<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Handles Quoted Printable (QP) Transfer Encoding in Swift Mailer using the PHP core function.
 *
 * @package    Swift
 * @subpackage Mime
 * @author     Lars Strojny
 */
class Swift_Mime_ContentEncoder_NativeQpContentEncoder implements Swift_Mime_ContentEncoder
{
    /**
     * @var null|string
     */
    private $charset;

    /**
     * @param string $charset
     */
    public function __construct($charset = 'utf-8')
    {
        $this->charset = $charset ?: 'utf-8';
    }

    /**
     * Notify this observer that the entity's charset has changed.
     *
     * @param string $charset
     */
    public function charsetChanged($charset)
    {
        $this->charset = $charset;
    }

    /**
     * Encode $in to $out.
     *
     * @param Swift_OutputByteStream $os              to read from
     * @param Swift_InputByteStream  $is              to write to
     * @param integer                $firstLineOffset
     * @param integer                $maxLineLength   0 indicates the default length for this encoding
     *
     * @throws RuntimeException
     */
    public function encodeByteStream(Swift_OutputByteStream $os, Swift_InputByteStream $is, $firstLineOffset = 0, $maxLineLength = 0)
    {
        if ($this->charset !== 'utf-8') {
            throw new RuntimeException(
                sprintf('Charset "%s" not supported. NativeQpContentEncoder only supports "utf-8"', $this->charset));
        }

        $string = '';

        while (false !== $bytes = $os->read(8192)) {
            $string .= $bytes;
        }

        $is->write($this->encodeString($string));
    }

    /**
     * Get the MIME name of this content encoding scheme.
     *
     * @return string
     */
    public function getName()
    {
        return 'quoted-printable';
    }

    /**
     * Encode a given string to produce an encoded string.
     *
     * @param string  $string
     * @param integer $firstLineOffset if first line needs to be shorter
     * @param integer $maxLineLength   0 indicates the default length for this encoding
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function encodeString($string, $firstLineOffset = 0, $maxLineLength = 0)
    {
        if ($this->charset !== 'utf-8') {
            throw new RuntimeException(
                sprintf('Charset "%s" not supported. NativeQpContentEncoder only supports "utf-8"', $this->charset));
        }

        return quoted_printable_encode($string);
    }
}
