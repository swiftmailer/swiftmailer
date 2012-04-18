<?php
/**
 * Stores serialized messages on the filesystem.
 *
 * Can make use if the igbinary and zlib extensions (if available) to
 * reduce the size of the serialized messages.
 *
 * @package Swift
 */
class Swift_SerializedFileSpool extends Swift_ConfigurableSpool
{

    // Not sure what the best strategy is for dealing with
    // io errors here. Throwing an exception or letting it
    // raise a php notice or warning chould cause more
    // issues as it would bubble up and potentially stop a
    // large amount of things from happening thereby putting
    // the application in a worse state then simply ignoreing
    // the error and carrying on.

    const MIN_RANDOM_PART_LENGTH = 10;

    private $_spoolDirectory;
    private $_spoolFilePrefix;
    private $_spoolFileExtension;
    private $_spoolFileMode;
    private $_retryLimit;
    private $_compress;
    private $_igbinary;

    public function __construct(
                        $spoolDirectory,
                        $spoolDirectoryCreateMode = 0700,
                        $spoolFilePrefix          = '',
                        $spoolFileExtension       = 'swift',
                        $spoolFileMode            = 0600,
                        $retryLimit               = 10,
                        $compress                 = TRUE,
                        $igbinary                 = TRUE
                    )
    {
        $this->_spoolDirectory     = (string) $spoolDirectory;
        $this->_spoolFilePrefix    = (string) $spoolFilePrefix;
        $this->_spoolFileExtension = '.'.$spoolFileExtension;
        $this->_spoolFileMode      = (integer) $spoolFileMode;
        $this->_retryLimit         = max(1, (integer) $retryLimit);
        $this->_compress           = (boolean) $compress;
        $this->_igbinary           = (boolean) $igbinary;
        if(strlen($this->_spoolFileExtension) <= 1)
        {
            throw new InvalidArgumentException('spoolFileExtension must not be an empty string.');
        }
        if(!file_exists($this->_spoolDirectory))
        {
            // Ignore the return code here as we check for the dir
            // below. This allows another process to create the dir
            // between the file_exists check above and the mkdir operation.
            @mkdir($this->_spoolDirectory, $spoolDirectoryCreateMode, TRUE);
        }
        if(!is_dir($this->_spoolDirectory))
        {
            throw new Swift_IoException(
                          'Specified spool directory ['
                          .$this->_spoolDirectry
                          .'] is not a valid directory path.'
                      );
        }
        $this->_spoolDirectory = realpath($this->_spoolDirectory).DIRECTORY_SEPARATOR;
    }

    public function start()
    {
    }

    public function stop()
    {
    }

    public function isStarted()
    {
        return TRUE;
    }

    public function queueMessage(Swift_Mime_Message $message)
    {
        $attempt   = 0;
        $filename  = $this->_spoolDirectory
                   . $this->_spoolFilePrefix
                   . $this->getRandomString(self::MIN_RANDOM_PART_LENGTH)
                   ;
        while($attempt < $this->_retryLimit)
        {
            $attempt++;
            $fullFilename = $filename.$this->_spoolFileExtension;
            $fp           = @fopen($fullFilename, 'x+b');
            if(!is_resource($fp))
            {
                $filename .= $this->getRandomString(1);
                continue;
            }
            try
            {
                if(!$this->serializeMessage($fp, $message))
                {
                    if(is_resource($fp))
                    {
                        @ftruncate($fp, 0);
                        @fclose($fp);
                        $fp = NULL;
                    }
                    @unlink($fullFilename);
                    continue;
                }
                if(is_resource($fp))
                {
                    @fclose($fp);
                }
                $fp = NULL;
                chmod($fullFilename, $this->_spoolFileMode);
                return TRUE;
            }
            catch(Exception $e)
            {
                if(is_resource($fp))
                {
                    @ftruncate($fp, 0);
                    @fclose($fp);
                    $fp = NULL;
                }
                @unlink($fullFilename);
                throw $e;
            }
        }
        throw new Swift_IoException('Retry limit reached while trying to queue mesage.');
    }

    public function flushQueue(Swift_Transport $transport, &$failedRecipients = NULL)
    {
        if(!$transport->isStarted())
        {
            $transport->start();
        }
        $failedRecipients = (array) $failedRecipients;
        $count            = 0;    // Number of emails/recipients sent to (can be more than 1 per message)
        $messages         = 0;    // Number of message in the spool processed.
        $prefixLength     = strlen($this->_spoolFilePrefix);
        $extensionLength  = strlen($this->_spoolFileExtension);
        $startTime        = time();
        $messageLimit     = $this->getMessageLimit();
        $timeLimit        = $this->getTimeLimit();
        $dir              = dir($this->_spoolDirectory);
        /* @var $dir Directory */
        while(($file = $dir->read()) !== FALSE)
        {
            if($messageLimit && $messages >= $messageLimit)
            {
                break;
            }
            if($timeLimit && (time() - $startTime) >= $timeLimit)
            {
                break;
            }
            if(substr($file, -$extensionLength) != $this->_spoolFileExtension)
            {
                continue;
            }
            if($prefixLength && substr($file, $prefixLength) != $this->_spoolFilePrefix)
            {
                continue;
            }
            $fullFilename        = $this->_spoolDirectory.$file;
            $fullSendingFilename = $fullFilename.'.sending';
            if(!@rename($fullFilename, $fullSendingFilename))
            {
                continue;
            }
            $fp = @fopen($fullSendingFilename, 'rb');
            if(!is_resource($fp))
            {
                continue;
            }
            try
            {
                $message = $this->unserializeMessage($fp);
                if(is_resource($fp))
                {
                    @fclose($fp);
                }
                $fp = NULL;
            }
            catch(Exception $e)
            {
                if(is_resource($fp))
                {
                    @fclose($fp);
                    $fp = NULL;
                }
                throw $e;
            }
            if(!$message instanceof Swift_Mime_Message)
            {
                continue;
            }
            $count += $transport->send($message, $failedRecipients);
            $messages++;
            @unlink($fullSendingFilename);
        }
        $dir->close();
        return $count;
    }

    public function recover($timeout = 900)
    {
        $prefixLength    = strlen($this->_spoolFilePrefix);
        $extension       = $this->_spoolFileExtension.'.sending';
        $extensionLength = strlen($extension);
        $dir             = dir($this->_spoolDirectory);
        $now             = time();
        while(($file = $dir->read()) !== FALSE)
        {
            if(substr($file, -$extensionLength) != $extension)
            {
                continue;
            }
            if($prefixLength && substr($file, $prefixLength) != $this->_spoolFilePrefix)
            {
                continue;
            }
            $fullSendingFilename = $this->_spoolDirectory.$file;
            $fullFilename        = substr($fullSendingFilename, 0, -8);
            $lockedTime          = @filectime($fullSendingFilename);
            if($lockedTime && ($now - $lockedTime) > $timeout)
            {
                @rename($fullSendingFilename, $fullFilename);
            }
        }
    }

    /**
     * Serialise a message to a file pointer.
     *
     * This method MUST NOT close the file pointer.
     * @param resource $fp
     * @param Swift_Mime_Message $message
     * @return boolean
     */
    protected function serializeMessage($fp, Swift_Mime_Message $message)
    {
        if($this->_igbinary && function_exists('igbinary_serialize'))
        {
            $data = igbinary_serialize($message);
        }
        else
        {
            $data = serialize($message);
        }
        if($data === FALSE)
        {
            return FALSE;
        }
        if($this->_compress && function_exists('gzcompress'))
        {
            $data = gzcompress($data);
            if($data === FALSE)
            {
                return FALSE;
            }
        }
        while(strlen($data))
        {
            $len = @fwrite($fp, $data);
            if($len < 1)
            {
                return FALSE;
            }
            $data = substr($data, $len);
        }
        return TRUE;
    }

    /**
     * Load a message from a read only file pointer.
     *
     * This method MUST NOT close the file pointer.
     * @param resource $fp
     * @return Swift_Mime_Message
     */
    protected function unserializeMessage($fp)
    {
        $data = @stream_get_contents($fp);
        if($data === FALSE)
        {
            return NULL;
        }
        if($this->_compress && function_exists('gzuncompress'))
        {
            $data = @gzuncompress($data);
            if($data === FALSE)
            {
                return NULL;
            }
        }
        if($this->_igbinary && function_exists('igbinary_unserialize'))
        {
            $data = igbinary_unserialize($data);
        }
        else
        {
            $data = unserialize($data);
        }
        if($data instanceof Swift_Mime_Message)
        {
            return $data;
        }
        return NULL;
    }

    protected function getRandomString($length)
    {
        $base          = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $baseMaxOffset = 35;
        $string        = '';
        for($i = 0; $i < $length; ++$i)
        {
            $string .= $base[ mt_rand(0, $baseMaxOffset) ];
        }
        return $string;
    }

}