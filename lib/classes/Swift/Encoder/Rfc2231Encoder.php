<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Handles RFC 2231 specified Encoding in Swift Mailer.
 *
 * @package    Swift
 * @subpackage Encoder
 * @author     Chris Corbyn
 */
class Swift_Encoder_Rfc2231Encoder implements Swift_Encoder
{
    /**
     * A character stream to use when reading a string as characters instead of bytes.
     *
     * @var Swift_CharacterStream
     */
    private $_charStream;

    /**
     * Creates a new Rfc2231Encoder using the given character stream instance.
     *
     * @param Swift_CharacterStream
     */
    public function __construct(Swift_CharacterStream $charStream)
    {
        $this->_charStream = $charStream;
    }

    /**
     * Takes an unencoded string and produces a string encoded according to
     * RFC 2231 from it.
     *
     * @param string  $string
     * @param integer $firstLineOffset
     * @param integer $maxLineLength   optional, 0 indicates the default of 75 bytes
     *
     * @return string
     */
    public function encodeString($string, $firstLineOffset = 0, $maxLineLength = 0)
    {
        $lines = array(); $lineCount = 0;
        $lines[] = '';
        $currentLine =& $lines[$lineCount++];

        if (0 >= $maxLineLength) {
            $maxLineLength = 75;
        }

        $this->_charStream->flushContents();
        $this->_charStream->importString($string);

        $thisLineLength = $maxLineLength - $firstLineOffset;

        while (false !== $char = $this->_charStream->read(4)) {
            $encodedChar = rawurlencode($char);
            if (0 != strlen($currentLine)
                && strlen($currentLine . $encodedChar) > $thisLineLength)
            {
                $lines[] = '';
                $currentLine =& $lines[$lineCount++];
                $thisLineLength = $maxLineLength;
            }
            $currentLine .= $encodedChar;
        }

        return implode("\r\n", $lines);
    }

    /**
     * Updates the charset used.
     *
     * @param string $charset
     */
    public function charsetChanged($charset)
    {
        $this->_charStream->setCharacterSet($charset);
    }
}
