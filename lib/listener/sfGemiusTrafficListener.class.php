<?php

/**
 * Event listener for sfGemiusTrafficPlugin.
 * 
 * @package     sfGemiusTrafficPlugin
 * @subpackage  listener
 * @author      Tomasz Jakub Rup <tomasz.rup@gmail.com>
 */
class sfGemiusTrafficListener
{
  /**
   * Get the current tracker object.
   * 
   * @param   sfEvent $event
   * 
   * @return  bool
   */
  public static function observe(sfEvent $event)
  {
    switch ($event['method'])
    {
      case 'getGemiusTrafficTracker':
      $event->setReturnValue(sfGemiusTrafficMixin::getTracker());
      return true;
      
      case 'setGemiusTrafficTracker':
      sfGemiusTrafficMixin::setTracker($event['arguments'][0]);
      return true;
    }
  }
  
}
