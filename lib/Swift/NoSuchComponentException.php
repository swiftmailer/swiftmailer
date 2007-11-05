<?php

/**
 * An exception thrown when a component is referenced which doesn't exist.
 * @author Chris Corbyn
 * @package Swift
 * @subpackage DI
 */
class Swift_NoSuchComponentException extends Exception
{
  
  /**
   * Create a new NoSuchComponentException with $message.
   * @param string $message
   */
  public function __construct($message)
  {
    parent::__construct($message);
  }
  
}
