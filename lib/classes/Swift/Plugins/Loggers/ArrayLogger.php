<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Logs to an Array backend.
 *
 * @author Chris Corbyn
 */
class Swift_Plugins_Loggers_ArrayLogger implements Swift_Plugins_Logger
{
    /**
     * The log contents.
     *
     * @var array
     */
    private $log = array();

    /**
     * Max size of the log.
     *
     * @var int
     */
    private $size = 0;

    /**
     * Create a new ArrayLogger with a maximum of $size entries.
     *
     * @var int
     */
    public function __construct($size = 50)
    {
        $this->size = $size;
    }

    /**
     * {@inheritdoc}
     */
    public function add(String $entry)
    {
        $this->log[] = $entry;
        while (count($this->log) > $this->size) {
            array_shift($this->log);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->log = array();
    }

    /**
     * {@inheritdoc}
     */
    public function dump(): string
    {
        return implode(PHP_EOL, $this->log);
    }
}
