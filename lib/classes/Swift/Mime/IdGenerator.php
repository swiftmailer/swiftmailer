<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Egulias\EmailValidator\EmailValidator;

/**
 * Message ID generator.
 */
class Swift_Mime_IdGenerator implements Swift_IdGenerator
{
    /**
     * @param EmailValidator $emailValidator
     * @param string|null    $idRight
     */
    public function __construct(EmailValidator $emailValidator, $idRight = null)
    {
        if ($idRight) {
            $this->idRight = $idRight;
        } else {
            $this->idRight = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'swift.generated';
            if (!$emailValidator->isValid('dummy@'.$this->idRight)) {
                $this->idRight = 'swift.generated';
            }
        }
    }

    /**
     * Returns the right-hand side of the "@" used in all generated IDs.
     *
     * @return string
     */
    public function getIdRight()
    {
        return $this->idRight;
    }

    /**
     * Sets the right-hand side of the "@" to use in all generated IDs.
     *
     * @param string $idRight
     */
    public function setIdRight($idRight)
    {
        $this->idRight = $idRight;
    }

    /**
     * @return string
     */
    public function generateId()
    {
        $idLeft = md5(getmypid().'.'.time().'.'.uniqid(mt_rand(), true));

        return $idLeft.'@'.$this->idRight;
    }
}
