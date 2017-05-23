<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A reporter which "collects" failures for the Reporter plugin.
 *
 * @author Chris Corbyn
 */
class Swift_Plugins_Reporters_HitReporter implements Swift_Plugins_Reporter
{
    /**
     * The list of failures.
     *
     * @var array
     */
    private $failures = array();

    private $failures_cache = array();

    /**
     * {@inheritdoc}
     */
    public function notify(Swift_Mime_SimpleMessage $message, string $address, int $result)
    {
        if (self::RESULT_FAIL === $result && !isset($this->failures_cache[$address])) {
            $this->failures[] = $address;
            $this->failures_cache[$address] = true;
        }
    }

    /**
     * Get an array of addresses for which delivery failed.
     *
     * @return array
     */
    public function getFailedRecipients(): array
    {
        return $this->failures;
    }

    /**
     * Clear the buffer (empty the list).
     */
    public function clear()
    {
        $this->failures = $this->failures_cache = array();
    }
}
