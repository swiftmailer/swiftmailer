<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Mime;

use Swift\Encoder;

/**
 * Interface for all Header Encoding schemes.
 *
 * @author Chris Corbyn
 */
interface HeaderEncoder extends Encoder
{
    /**
     * Get the MIME name of this content encoding scheme.
     *
     * @return string
     */
    public function getName();
}
