<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift;

use Swift\Transport\LoadBalancedTransport as BaseLoadBalancedTransport;

/**
 * Redundantly and rotationally uses several Transport implementations when sending.
 *
 * @author Chris Corbyn
 */
class LoadBalancedTransport extends BaseLoadBalancedTransport
{
    /**
     * Creates a new LoadBalancedTransport with $transports.
     *
     * @param array $transports
     */
    public function __construct($transports = [])
    {
        call_user_func_array(
            [$this, '\\Swift\\Transport\\LoadBalancedTransport::__construct'],
            DependencyContainer::getInstance()
                ->createDependenciesFor('transport.loadbalanced')
            );

        $this->setTransports($transports);
    }
}
