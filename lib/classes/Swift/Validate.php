<?php
/*
 * This file is part of SwiftMailer.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Utility Class allowing users to simply check expressions again Swift Grammar.
 *
 * @author  Xavier De Cock <xdecock@gmail.com>
 */
class Swift_Validate
{
    /**
     * Checks if an e-mail address matches the current grammars.
     *
     * @param string $email
     *
     * @return bool
     */
    public static function email($email)
    {
        $validator = new Swift_EmailValidatorBridge();
        $isValid = $validator->isValid($email);

        return $isValid;
    }
}
