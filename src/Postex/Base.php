<?php
namespace Postex;

use ReflectionMethod;
use ReflectionProperty;

/**
 * Class Base
 * @package Postex
 */
class Base
{
    /**
     * Marker for before send request event
     */
    const EVENT_BEFORE_SEND_REQUEST = 'beforeSendRequest';

    /**
     * Marker for after send request event
     */
    const EVENT_AFTER_SEND_REQUEST = 'afterSendRequest';

    /**
     * @var Config the configuration object injected into the application at runtime
     */
    private static $_config;

    /**
     * @var Params the package registry that will hold various components
     */
    private static $_registry = array();

    /**
     * @var Params the registered event handlers
     */
    private static $_eventHandlers = array();

    /**
     * Inject the configuration into the sdk
     *
     * @param Config $config
     */
    public static function setConfig(Config $config)
    {
        self::$_config = $config;
    }

    /**
     * Returns the configuration object
     *
     * @return Config
     */
    public static function getConfig()
    {
        return self::$_config;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     * @throws \Exception
     */
    public function addToRegistry($key, $value)
    {
        $this->getRegistry()->add($key, $value);

        return $this;
    }

    /**
     * @return Params
     * @throws \Exception
     */
    public function getRegistry()
    {
        if (!(self::$_registry instanceof Params)) {
            self::$_registry = new Params(self::$_registry);
        }

        return self::$_registry;
    }

    /**
     * @param array $components
     *
     * @return $this
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function setComponents(array $components)
    {
        foreach ($components as $componentName => $config) {
            $this->setComponent($componentName, $config);
        }

        return $this;
    }

    /**
     * @param       $componentName
     * @param array $config
     *
     * @return $this
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function setComponent($componentName, array $config)
    {
        if (empty($config['class'])) {
            throw new Exception('Please set the class property for "' . htmlspecialchars($componentName, ENT_QUOTES, $this->getConfig()->getCharset()) . '" component.');
        }

        $component = new $config['class'];

        if ($component instanceof Base) {
            $component->populateFromArray($config);
        } else {
            unset($config['class']);
            foreach ($config as $property => $value) {
                if (property_exists($component, $property)) {
                    $reflection = new ReflectionProperty($component, $property);
                    if ($reflection->isPublic()) {
                        $component->$property = $value;
                    }
                }
            }
        }
        $this->addToRegistry($componentName, $component);

        return $this;
    }

    /**
     * @param array $eventHandlers
     *
     * @return $this
     * @throws \Exception
     */
    public function setEventHandlers(array $eventHandlers)
    {
        foreach ($eventHandlers as $eventName => $callback) {
            if (empty($callback) || !is_array($callback)) {
                continue;
            }
            if (!is_array($callback[0]) && is_callable($callback)) {
                $this->getEventHandlers($eventName)->add(null, $callback);
                continue;
            }
            if (is_array($callback[0])) {
                foreach ($callback as $cb) {
                    if (is_callable($cb)) {
                        $this->getEventHandlers($eventName)->add(null, $cb);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param $eventName
     *
     * @return mixed
     * @throws \Exception
     */
    public function getEventHandlers($eventName)
    {
        if (!(self::$_eventHandlers instanceof Params)) {
            self::$_eventHandlers = new Params(self::$_eventHandlers);
        }

        if (!self::$_eventHandlers->contains($eventName) || !(self::$_eventHandlers->itemAt($eventName) instanceof Params)) {
            self::$_eventHandlers->add($eventName, new Params());
        }

        return self::$_eventHandlers->itemAt($eventName);
    }

    /**
     * @param $eventName
     *
     * @return $this
     * @throws \Exception
     */
    public function removeEventHandlers($eventName)
    {
        self::$_eventHandlers->remove($eventName);

        return $this;
    }

    /**
     * @param array $params
     *
     * @return $this
     * @throws \ReflectionException
     */
    protected function populateFromArray(array $params = array())
    {
        foreach ($params as $name => $value) {

            $found = false;

            if (property_exists($this, $name)) {
                $param = $name;
            } else {
                $asSetterName    = str_replace('_', ' ', $name);
                $asSetterName    = ucwords($asSetterName);
                $asSetterName    = str_replace(' ', '', $asSetterName);
                $asSetterName{0} = strtolower($asSetterName{0});
                $param           = property_exists($this, $asSetterName) ? $asSetterName : null;
            }

            if ($param) {
                $reflection = new ReflectionProperty($this, $param);
                if ($reflection->isPublic()) {
                    $this->$param = $value;
                    $found        = true;
                }
            }

            if (!$found) {
                $methodName = str_replace('_', ' ', $name);
                $methodName = ucwords($methodName);
                $methodName = str_replace(' ', '', $methodName);
                $methodName = 'set' . $methodName;

                if (method_exists($this, $methodName)) {
                    $reflection = new ReflectionMethod($this, $methodName);
                    if ($reflection->isPublic()) {
                        $this->$methodName($value);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param $name
     * @param $value
     *
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function __set($name, $value)
    {
        $methodName = 'set' . ucfirst($name);
        if (!method_exists($this, $methodName)) {
            $this->addToRegistry($name, $value);
        } else {
            $method = new ReflectionMethod($this, $methodName);
            if ($method->isPublic()) {
                $this->$methodName($value);
            }
        }
    }

    /**
     * @param $name
     *
     * @return mixed
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function __get($name)
    {
        $methodName = 'get' . ucfirst($name);
        if (!method_exists($this, $methodName) && $this->getRegistry()->contains($name)) {
            return $this->getRegistry()->itemAt($name);
        } elseif (method_exists($this, $methodName)) {
            $method = new ReflectionMethod($this, $methodName);
            if ($method->isPublic()) {
                return $this->$methodName();
            }
        }
    }
}