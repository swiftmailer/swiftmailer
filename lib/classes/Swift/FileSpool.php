<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier <fabien.potencier@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Stores Messages on the filesystem.
 * @package Swift
 * @author  Fabien Potencier
 * @author Xavier De Cock <xdecock@gmail.com>
 */
class Swift_FileSpool extends Swift_ConfigurableSpool
{
  /** The spool directory */
  private $_path;
  
  /**
   * File WriteRetry Limit
   * @var int
   */
  private $_retryLimit=10;
  
  /**
   * Create a new FileSpool.
   * @param string $path
   */
  public function __construct($path)
  {
    $this->_path = $path;
    
    if (!file_exists($this->_path))
    {
      if (!mkdir($this->_path, 0777, true))
      {
        throw new Swift_SwiftException('Unable to create Path ['.$this->_path.']');
      }
    }
  }
  
  /**
   * Tests if this Spool mechanism has started.
   *
   * @return boolean
   */
  public function isStarted()
  {
    return true;
  }
  
  /**
   * Starts this Spool mechanism.
   */
  public function start()
  {
  }
  
  /**
   * Stops this Spool mechanism.
   */
  public function stop()
  {
  }
  
  /**
   * Queues a message.
   * @param Swift_Mime_Message $message The message to store
   * @return boolean
   */
  public function queueMessage(Swift_Mime_Message $message)
  {
    $ser = serialize($message);
    $fileName=$this->_path.'/'.$this->getRandomString(10);
    for ($i = 0; $i < $this->_retryLimit; ++$i) 
    {
      /* We try an exclusive creation of the file
       * This is an atomic operation, it avoid locking mechanism
       */
      $fp=fopen($fileName.'.message', 'x');
      if ($fp) 
      {
        $fp=fwrite($ser);
        fclose($fp);
        return true;
      } 
      else 
      {
        /* The file allready exists, we try a longer fileName
         */
        $fileName.=$this->getRandomString(1);
      }
    }
    return false;
  }
  
  /**
   * Execute a recovery if for anyreason a process is sending for too long
   * 
   * @param int $timeout in second Defaults is for very slow smtp responses
   */
  public function recover($timeout=900)
  {
    foreach (new DirectoryIterator($this->_path) as $file)
    {
      $file = $file->getRealPath();

      if (substr($file, -16)=='.message.sending')
      {
        $lockedtime=filectime($file);
        if ((time()-$lockedtime)>$timeout) 
        {
          rename($file, substr($file, 0, -8));
        }
      }
    }    
  }
  
  /**
   * Sends messages using the given transport instance.
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
      $file = $file->getRealPath();

      if (substr($file, -8)=='.message')
      {
        continue;
      }

      /* We try a rename, it's an atomic operation, and avoid locking the file */
      if (rename($file, $file.'.sending')) 
      {
        $message = unserialize(file_get_contents($file.'.sending'));

        $count += $transport->send($message, $failedRecipients);

        unlink($file.'.sending');
      }
      else 
      {
        /* This message has just been catched by another process */
        continue;
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
   * Returns a random string needed to generate a fileName for the queue.
   * @param int $count
   */
  protected function getRandomString($count) {
    // This string MUST stay FS safe, avoid special chars
    $base="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-.";
    $ret='';
    $strlen=strlen($base);
    for ($i=0; $i<$count; ++$i) 
    {
      $ret.=$base[((int)rand(0,$base-1))];
    }
    return $ret;
  }
}
