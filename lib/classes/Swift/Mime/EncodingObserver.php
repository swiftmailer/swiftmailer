<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Mime;

/**
 * Observes changes for a Mime entity's ContentEncoder.
 *
 * @author Chris Corbyn
 */
interface EncodingObserver
{
    /**
     * Notify this observer that the observed entity's ContentEncoder has changed.
     *
     * @param ContentEncoder $encoder
     */
    public function encoderChanged(ContentEncoder $encoder);
}
