<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Uses several mapped Transport implementations when sending.
 *
 * @package    Swift
 * @subpackage Transport
 * @author     Patrick McAndrew <patrick@urg.name>
 */
class Swift_MappedTransport extends Swift_Transport_MappedTransport
{
    /**
     * Creates a new MappedTransport with $transports.
     *
     * @param array $transports
     */
    public function __construct($transports = array())
    {
        call_user_func_array(
            array($this, 'Swift_Transport_MappedTransport::__construct'),
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('transport.mapped')
            );

        $this->setTransports($transports);
    }

    /**
     * Create a new MappedTransport instance.
     *
     * @param array $transports
     *
     * @return Swift_MappedTransport
     */
    public static function newInstance($transports = array())
    {
        return new self($transports);
    }
}
