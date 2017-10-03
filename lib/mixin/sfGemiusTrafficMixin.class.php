<?php

/**
 * Mixins for sfGemiusTrafficPlugin.
 *
 * @author      Tomasz Jakub Rup <tomasz.rup@gmail.com>
 */
class sfGemiusTrafficMixin
{
    /**
     * Get the current request's tracker object.
     *
     * @return sfGemiusTrafficTracker
     */
    public static function getTracker()
    {
        return sfContext::getInstance()->getRequest()->getAttribute('gemius_traffic_tracker', null, 'sf_gemius_traffic_plugin');
    }

    /**
     * Set the current request's tracker object.
     *
     * @param sfGemiusTrafficTracker $tracker
     */
    public static function setTracker(sfGemiusTrafficTracker $tracker)
    {
        sfContext::getInstance()->getRequest()->setAttribute('gemius_traffic_tracker', $tracker, 'sf_gemius_traffic_plugin');
    }
}
