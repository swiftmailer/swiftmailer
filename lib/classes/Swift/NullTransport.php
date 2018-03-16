<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier <fabien.potencier@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift;

use Swift\Transport\NullTransport as BaseNullTransport;

/**
 * Pretends messages have been sent, but just ignores them.
 *
 * @author Fabien Potencier
 */
class NullTransport extends BaseNullTransport
{
    public function __construct()
    {
        call_user_func_array(
            [$this, '\\Swift\\Transport\\NullTransport::__construct'],
            DependencyContainer::getInstance()
                ->createDependenciesFor('transport.null')
        );
    }
}
