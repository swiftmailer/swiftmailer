<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Plugins\Reporters;

use Swift\Plugins\Reporter;
use Swift\Mime\SimpleMessage;

/**
 * A reporter which "collects" failures for the Reporter plugin.
 *
 * @author Chris Corbyn
 */
class HitReporter implements Reporter
{
    /**
     * The list of failures.
     *
     * @var array
     */
    private $failures = [];

    private $failures_cache = [];

    /**
     * Notifies this ReportNotifier that $address failed or succeeded.
     *
     * @param string $address
     * @param int    $result  from {@link RESULT_PASS, RESULT_FAIL}
     */
    public function notify(SimpleMessage $message, $address, $result)
    {
        if (self::RESULT_FAIL == $result && !isset($this->failures_cache[$address])) {
            $this->failures[] = $address;
            $this->failures_cache[$address] = true;
        }
    }

    /**
     * Get an array of addresses for which delivery failed.
     *
     * @return array
     */
    public function getFailedRecipients()
    {
        return $this->failures;
    }

    /**
     * Clear the buffer (empty the list).
     */
    public function clear()
    {
        $this->failures = $this->failures_cache = [];
    }
}
