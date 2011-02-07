<?php
/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier <fabien.potencier@gmail.com>
 * (c) 2011 Charles SANQUER  <charles.sanquer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * Mailer File Spool with message lock system
 * to prevent sending each mails more than 1 time
 *
 * based on original Swift_FileSpool code by Fabien Potencier
 *
 * @package Swift
 * @author Charles SANQUER
 *
 */
class Swift_LockedFileSpool extends Swift_FileSpool
{
    /**
     * @var int number of seconds
     */
    protected $lockTimeLimit = 0;

    /**
     * Create a new LockedFileSpool.
     *
     * @param string $path
     * @param int $lockTimeLimit default = 7200 , number of seconds
     */
    public function __construct($path, $lockTimeLimit = 7200)
    {
        parent::__construct($path);

        $this->setLockTimeLimit($lockTimeLimit);
    }

    /**
     * Sends messages using the given transport instance and checking messages are not locked.
     *
     * @param Swift_Transport $transport         A transport instance
     * @param string[]        &$failedRecipients An array of failures by-reference
     *
     * @return int The number of sent emails
     */
    public function flushQueue(Swift_Transport $transport, &$failedRecipients = null)
    {
        if (!$transport->isStarted())
        {
            $transport->start();
        }

        $failedRecipients = (array) $failedRecipients;
        $count = 0;
        $time = time();

        foreach (new DirectoryIterator($this->_path) as $file)
        {
            $filesimple = $file;
            $file = $file->getRealPath();

            if (!strpos($file, '.message'))
            {
                continue;
            }

            $pathWithoutExt= substr($file, 0, strrpos($file, '.message'));
            if (!$this->isLockValid($pathWithoutExt))
            {
                $this->addLock($pathWithoutExt);
                $message = unserialize(file_get_contents($file));

                $count += $transport->send($message, $failedRecipients);

                unlink($file);
                if (!file_exists($file))
                {
                    $this->removeLock($pathWithoutExt);
                }
            }

            if ($this->getMessageLimit() && $count >= $this->getMessageLimit())
            {
                break;
            }

            if ($this->getTimeLimit() && (time() - $time) >= $this->getTimeLimit())
            {
                break;
            }
        }

        return $count;
    }

    /**
     *
     * @param int $lockTimeLimit
     */
    public function setLockTimeLimit($lockTimeLimit)
    {
        $this->lockTimeLimit = (int) $lockTimeLimit;
    }

    /**
     *
     * @return int number of seconds
     */
    public function getLockTimeLimit()
    {
        return (int) $this->lockTimeLimit;
    }

    /**
     * check if a file has a lock file
     *
     * @param string $filePath
     *
     * @return bool
     */
    protected function hasLock($filePath)
    {
        return file_exists($filePath.'.lock');
    }

    /**
     * check if a file has a lock file and the lock file has not expired
     *
     * @see spyritFileSpool::getLockTimeLimit()
     * @see spyritFileSpool::setLockTimeLimit()
     *
     * @param string $filePath
     *
     * @return bool
     */
    protected function isLockValid($filePath)
    {
        if (!$this->hasLock($filePath))
        {
            return false;
        }

        return (time() - filemtime($filePath.'.lock')) < $this->getLockTimeLimit();
    }

    /**
     * add a lock file if needed
     *
     * @param string $filePath
     *
     * @return bool true if lock has been created or already exists
     */
    protected function addLock($filePath)
    {
        if (!$this->hasLock($filePath))
        {
            $dir = dirname($filePath);
            if (!file_exists($dir))
            {
                mkdir($dir, 0777, true);
            }

            touch($filePath.'.lock');
        }

        return $this->hasLock($filePath);
    }

    /**
     * remove a lock file if it exists
     *
     * @param string $filePath
     *
     * @return bool true if lock file doesn't exist anymore
     */
    protected function removeLock($filePath)
    {
        if ($this->hasLock($filePath))
        {
            unlink($filePath.'.lock');
        }

        return !$this->hasLock($filePath);
    }
}
