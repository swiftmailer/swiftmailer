<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Mime;

use Swift\IdGenerator as BaseIdGenerator;

/**
 * Message ID generator.
 */
class IdGenerator implements BaseIdGenerator
{
    /**
     * @param string $idRight
     */
    public function __construct($idRight)
    {
        $this->idRight = $idRight;
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
        $idLeft = bin2hex(random_bytes(16)); // set 32 hex values

        return $idLeft.'@'.$this->idRight;
    }
}
