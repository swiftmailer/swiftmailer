<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Changes some global preference settings in Swift Mailer.
 *
 * @author Chris Corbyn
 */
class Swift_Preferences
{
    /** Singleton instance */
    private static $instance = null;

    /** Constructor not to be used */
    private function __construct()
    {
    }

    /**
     * Gets the instance of Preferences.
     *
     * @return self
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Set the default charset used.
     *
     * @param string $charset
     *
     * @return $this
     */
    public function setCharset($charset)
    {
        Swift_DependencyContainer::getInstance()->register('properties.charset')->asValue($charset);

        return $this;
    }

    /**
     * Set the directory where temporary files can be saved.
     *
     * @param string $dir
     *
     * @return $this
     */
    public function setTempDir($dir)
    {
        Swift_DependencyContainer::getInstance()->register('tempdir')->asValue($dir);

        return $this;
    }

    /**
     * Set the type of cache to use (i.e. "disk" or "array").
     *
     * @param string $type
     *
     * @return $this
     */
    public function setCacheType($type)
    {
        Swift_DependencyContainer::getInstance()->register('cache')->asAliasOf(sprintf('cache.%s', $type));

        return $this;
    }

    /**
     * Set the QuotedPrintable dot escaper preference.
     *
     * @param bool $dotEscape
     *
     * @return $this
     */
    public function setQPDotEscape($dotEscape)
    {
        $dotEscape = !empty($dotEscape);
        Swift_DependencyContainer::getInstance()
            ->register('mime.qpcontentencoder')
            ->asNewInstanceOf('Swift_Mime_ContentEncoder_QpContentEncoder')
            ->withDependencies(['mime.charstream', 'mime.bytecanonicalizer'])
            ->addConstructorValue($dotEscape);

        return $this;
    }
}
