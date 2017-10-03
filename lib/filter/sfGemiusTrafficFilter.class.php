<?php

/**
 * Add tracking code to the response.
 *
 * @author      Tomasz Jakub Rup <tomasz.rup@gmail.com>
 */
class sfGemiusTrafficFilter extends sfFilter
{
    /**
     * Insert tracking code for applicable web requests.
     *
     * @param   sfFilterChain $filterChain
     */
    public function execute($filterChain)
    {
        $prefix   = 'app_gemius_traffic_plugin_';
        $user     = $this->context->getUser();
        $request  = $this->context->getRequest();
        $response = $this->context->getResponse();

        if ($this->isFirstCall()) {
            $tracker = new sfGemiusTrafficTracker($this->context);

            // pull callables from session storage
            $callables = $user->getAttribute('callables', array(), 'sf_gemius_traffic_plugin');
            foreach ($callables as $callable) {
                list($method, $arguments) = $callable;
                call_user_func_array(array($tracker, $method), $arguments);
            }

            $request->setGemiusTrafficTracker($tracker);
        }

        $filterChain->execute();
        $tracker = $request->getGemiusTrafficTracker();

        // apply module- and action-level configuration
        $module = $this->context->getModuleName();
        $action = $this->context->getActionName();

        $moduleParams = sfConfig::get('mod_'.strtolower($module).'_gemius_traffic_params', array());
        $tracker->configure($moduleParams);

        $actionConfig = sfConfig::get('mod_'.strtolower($module).'_'.$action.'_gemius_traffic', array());
        if (isset($actionConfig['params'])) {
            $tracker->configure($actionConfig['params']);
        }

        // insert tracking code
        if ($this->isTrackable() && $tracker->isEnabled()) {
            if (sfConfig::get('sf_logging_enabled')) {
                sfGemiusTrafficToolkit::logMessage($this, 'Inserting tracking code.');
            }

            $tracker->insert($response);
        } elseif (sfConfig::get('sf_logging_enabled')) {
            sfGemiusTrafficToolkit::logMessage($this, 'Tracking code not inserted.');
        }

        $user->getAttributeHolder()->removeNamespace('sf_gemius_plugin');
        $tracker->shutdown($user);
    }

    /**
     * Test whether the response is trackable.
     *
     * @return  bool
     */
    protected function isTrackable()
    {
        $request    = $this->context->getRequest();
        $response   = $this->context->getResponse();
        $controller = $this->context->getController();

        // don't add analytics:
        // * for XHR requests
        // * if not HTML
        // * if 304
        // * if not rendering to the client
        // * if HTTP headers only
        if ($request->isXmlHttpRequest() ||
        strpos($response->getContentType(), 'html') === false ||
        $response->getStatusCode() == 304 ||
        $controller->getRenderMode() != sfView::RENDER_CLIENT ||
        $response->isHeaderOnly()) {
            return false;
        } else {
            return true;
        }
    }
}
