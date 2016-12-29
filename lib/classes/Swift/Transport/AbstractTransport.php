<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Abstract Tranport class.
 *
 */
abstract class Swift_Transport_AbstractTransport
{
    /** Determine the best-use reverse path for this message */
    protected function _getReversePath(Swift_Mime_Message $message)
    {
        $return = $message->getReturnPath();
        $sender = $message->getSender();
        $from = $message->getFrom();
        $path = null;
        if (!empty($return)) {
            $path = $return;
        } elseif (!empty($sender)) {
            // Don't use array_keys
            reset($sender); // Reset Pointer to first pos
            $path = key($sender); // Get key
        } elseif (!empty($from)) {
            reset($from); // Reset Pointer to first pos
            $path = key($from); // Get key
        }

        return $path;
    }

    /**
     * Fix CVE-2016-10074 by disallowing potentially unsafe shell characters.
     *
     * Note that escapeshellarg and escapeshellcmd are inadequate for our purposes, especially on Windows.
     *
     * @param string $string The string to be validated
     *
     * @return bool
     */
    protected function _isShellSafe($string)
    {
        // Future-proof
        if (escapeshellcmd($string) !== $string || !in_array(escapeshellarg($string), array("'$string'", "\"$string\""))) {
            return false;
        }

        $length = strlen($string);
        for ($i = 0; $i < $length; ++$i) {
            $c = $string[$i];

            /*
             * All other ASCII symbols have a special meaning in at least one common
             * shell.  Notably, argument delimiters in cmd.exe include not only
             * whitespace, but also comma, equals, and semicolon.  Plus also needs
             * to be escaped in cmd.exe.
             *
             * ctype_alnum may allow non-Latin characters in some locales.  This
             * should not present a problem on a properly configured system, or even
             * most improperly configured systems.  If you need to enforce 7-bit
             * ASCII, set the current language to "C".
             */
            if (!ctype_alnum($c) && strpos('@_-.', $c) === false) {
                return false;
            }
        }

        return true;
    }
}
