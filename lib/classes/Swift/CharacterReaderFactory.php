<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift;

/**
 * A factory for creating CharacterReaders.
 *
 * @author Chris Corbyn
 */
interface CharacterReaderFactory
{
    /**
     * Returns a CharacterReader suitable for the charset applied.
     *
     * @param string $charset
     *
     * @return \Swift\CharacterReader
     */
    public function getReaderFor($charset);
}
