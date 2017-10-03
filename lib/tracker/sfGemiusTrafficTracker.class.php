<?php

/**
 *
 *
 * @author      Tomasz Jakub Rup <tomasz.rup@gmail.com>
 */
class sfGemiusTrafficTracker
{
    const POSITION_HEAD = 'head';
    const POSITION_BODY_TOP = 'top';
    const POSITION_BODY_BOTTOM = 'bottom';

    protected $enabled = false;
    protected $identifier = null;
    protected $insertion = self::POSITION_HEAD;

    public function __construct($parameters = array())
    {
        $this->initialize($parameters);
    }

    public function initialize($parameters = array())
    {
        $this->parameterHolder = class_exists('sfNamespacedParameterHolder') ? new sfNamespacedParameterHolder() : new sfParameterHolder();
        $this->parameterHolder->add($parameters);

        // apply configuration from app.yml
        $prefix = 'app_gemius_traffic_plugin_';

        $params = sfConfig::get($prefix.'params', array());
        $params['enabled'] = sfConfig::get($prefix.'enabled');
        $params['identifier'] = sfConfig::get($prefix.'identifier');
        $params['insertion'] = sfConfig::get($prefix.'insertion');

        $this->configure($params);

        return true;
    }

    /**
     * Apply non-null configuration values.
     *
     * @param array $params
     */
    public function configure($params)
    {
        $params = array_merge([
      'enabled' => null,
      'identifier' => null,
      'insertion' => null, ], $params);

        if (!is_null($params['enabled'])) {
            $this->setEnabled($params['enabled']);
        }
        if (!is_null($params['insertion'])) {
            $this->setInsertion($params['insertion']);
        }
        if (!is_null($params['identifier'])) {
            $this->setIdentifier($params['identifier']);
        }
    }

    public function getParameterHolder()
    {
        return $this->parameterHolder;
    }

    public function getParameter($name, $default = null, $ns = null)
    {
        return $this->parameterHolder->get($name, $default, $ns);
    }

    public function hasParameter($name, $ns = null)
    {
        return $this->parameterHolder->has($name, $ns);
    }

    public function setParameter($name, $value, $ns = null)
    {
        return $this->parameterHolder->set($name, $value, $ns);
    }

    /**
     * Toggle tracker's enabled state.
     *
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Add a custom tracking variable to this cookie.
     *
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Add a custom tracking variable to this cookie.
     *
     * @param string $profile_id
     */
    public function setInsertion($insertion)
    {
        $this->insertion = $insertion;
    }

    public function getInsertion()
    {
        return $this->insertion;
    }

    /**
     * Insert tracking code into a response.
     *
     * @param sfResponse $response
     */
    public function insert(sfResponse $response)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $html = [];
        $html[] = '<script type="text/javascript">';
        $html[] = '//<![CDATA[';
        $html[] = 'var gemius_identifier = new String(\''.$this->getIdentifier().'\');';
        $html[] = '//]]>';
        $html[] = '</script>';
        $html[] = '<script type="text/javascript" src="'.sfContext::getInstance()->getRequest()->getRelativeUrlRoot().'/sfGemiusTrafficPlugin/js/gemius.js"></script>';

        $html = implode("\n", $html);
        $this->doInsert($response, $html, $this->insertion);
    }

    /**
     * Insert content into a response.
     *
     * @param sfResponse $response
     * @param string $content
     * @param string $position
     */
    protected function doInsert(sfResponse $response, $content, $position = null)
    {
        if ($position == null) {
            $position = self::POSITION_HEAD;
        }

        // check for overload
        $method = 'doInsert'.$position;

        if (method_exists($this, $method)) {
            call_user_func([$this, $method], $response, $content);
        } else {
            $old = $response->getContent();

            switch ($position) {
        case self::POSITION_HEAD:
        $new = str_ireplace('</head>', "\n".$content."\n</head>", $old);

        break;

        case self::POSITION_BODY_TOP:
        $new = preg_replace('/<body[^>]*>/i', "$0\n".$content."\n", $old, 1);

        break;

        case self::POSITION_BODY_BOTTOM:
        $new = str_ireplace('</body>', "\n".$content."\n</body>", $old);

        break;
      }

            if ($old == $new) {
                $new .= $content;
            }

            $response->setContent($new);
        }
    }

    /**
     * Apply common options to a value.
     *
     * @param mixed $value
     * @param mixed $options
     *
     * @return bool whether to continue execution
     */
    protected function prepare(&$value, &$options = [])
    {
        if (is_string($options)) {
            $options = sfToolkit::stringToArray($options);
        }

        if (isset($options['use_flash']) && $options['use_flash']) {
            unset($options['use_flash']);

            $trace = debug_backtrace();

            $caller = $trace[1];
            $this->plant($caller['function'], [$value, $options]);

            return false;
        } else {
            if (is_string($value) && isset($options['is_route']) && $options['is_route']) {
                $value = $this->context->getController()->genUrl($value);
                unset($options['is_route']);
            }

            return true;
        }
    }

    /**
     * Plant a callable to be executed against the next request's tracker.
     *
     * @param string $method
     * @param array $arguments
     */
    protected function plant($method, $arguments = [])
    {
        if (sfConfig::get('sf_logging_enabled')) {
            sfGemiusTrafficToolkit::logMessage($this, 'Storing call to %s method for next response.');
        }

        $callables = $this->parameterHolder->getAll('flash', []);
        $callables[] = [$method, $arguments];

        $this->parameterHolder->removeNamespace('flash');
        $this->parameterHolder->add($callables, 'flash');
    }

    /**
     * Update storage with callables for the next tracker.
     *
     * @param sfUser $user
     */
    public function shutdown($user)
    {
        if (sfConfig::get('sf_logging_enabled')) {
            sfGemiusTrafficToolkit::logMessage($this, 'Copying callables to session storage.');
        }

        $user->getAttributeHolder()->set('callables', $this->parameterHolder->getAll('flash', []), 'sf_gemius_traffic_plugin');
    }
}
