<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier <fabien.potencier@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift;

use Swift\Transport\SpoolTransport as TransportSpoolTransport;

/**
 * Stores Messages in a queue.
 *
 * @author Fabien Potencier
 */
class SpoolTransport extends TransportSpoolTransport
{
    /**
     * Create a new SpoolTransport.
     */
    public function __construct(Spool $spool)
    {
        $arguments = Swift_DependencyContainer::getInstance()
            ->createDependenciesFor('transport.spool');

        $arguments[] = $spool;

        call_user_func_array(
            [$this, 'Swift_Transport_SpoolTransport::__construct'],
            $arguments
        );
    }
}
