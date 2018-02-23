<?php
namespace Postex;

use ArrayAccess;
use Countable;
use Exception;
use IteratorAggregate;
use Traversable;

/**
 * Class Params
 * @package Postex
 */
class Params extends Base implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * @var array internal data storage
     */
    private $_data = array();

    /**
     * @var boolean whether this list is read-only
     */
    private $_readOnly = false;

    /**
     * Params constructor.
     *
     * @param null $data
     * @param bool $readOnly
     *
     * @throws Exception
     */
    public function __construct($data = null, $readOnly = false)
    {
        if ($data !== null) {
            $this->copyFrom($data);
        }
        $this->setReadOnly($readOnly);
    }

    /**
     * @return boolean whether this map is read-only or not. Defaults to false.
     */
    public function getReadOnly()
    {
        return $this->_readOnly;
    }

    /**
     * @param boolean $value whether this list is read-only or not
     */
    protected function setReadOnly($value)
    {
        $this->_readOnly = $value;
    }

    /**
     * @return ParamsIterator|Traversable
     */
    public function getIterator()
    {
        return new ParamsIterator($this->_data);
    }

    /**
     * Returns the number of items in the map.
     * This method is required by Countable interface.
     * @return integer number of items in the map.
     */
    public function count()
    {
        return $this->getCount();
    }

    /**
     * Returns the number of items in the map.
     * @return integer the number of items in the map
     */
    public function getCount()
    {
        return count($this->_data);
    }

    /**
     * @return array the key list
     */
    public function getKeys()
    {
        return array_keys($this->_data);
    }

    /**
     * Returns the item with the specified key.
     * This method is exactly the same as {@link offsetGet}.
     *
     * @param mixed $key the key
     *
     * @return mixed the element at the offset, null if no element is found at the offset
     */
    public function itemAt($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }

    /**
     * @param $key
     * @param $value
     *
     * @throws Exception
     */
    public function add($key, $value)
    {
        if (!$this->_readOnly) {
            if ($key === null) {
                $this->_data[] = $value;
            } else {
                $this->_data[$key] = $value;
            }
        } else {
            throw new Exception('The params map is read only.');
        }
    }

    /**
     * @param $key
     *
     * @return mixed|null
     * @throws Exception
     */
    public function remove($key)
    {
        if (!$this->_readOnly) {
            if (isset($this->_data[$key])) {
                $value = $this->_data[$key];
                unset($this->_data[$key]);

                return $value;
            } else {
                // it is possible the value is null, which is not detected by isset
                unset($this->_data[$key]);

                return null;
            }
        } else {
            throw new Exception('The params map is read only.');
        }
    }

    /**
     * Removes all items in the map.
     */
    public function clear()
    {
        foreach (array_keys($this->_data) as $key) {
            $this->remove($key);
        }
    }

    /**
     * @param mixed $key the key
     *
     * @return boolean whether the map contains an item with the specified key
     */
    public function contains($key)
    {
        return isset($this->_data[$key]) || array_key_exists($key, $this->_data);
    }

    /**
     * @return array the list of items in array
     */
    public function toArray()
    {
        return $this->_data;
    }

    /**
     * @param $data
     *
     * @throws Exception
     */
    public function copyFrom($data)
    {
        if (is_array($data) || $data instanceof Traversable) {
            if ($this->getCount() > 0) {
                $this->clear();
            }
            if ($data instanceof Params) {
                $data = $data->_data;
            }
            foreach ($data as $key => $value) {
                $this->add($key, $value);
            }
        } elseif ($data !== null) {
            throw new Exception('Postex\Params map data must be an array or an object implementing Traversable.');
        }
    }

    /**
     * @param      $data
     * @param bool $recursive
     *
     * @throws Exception
     */
    public function mergeWith($data, $recursive = true)
    {
        if (is_array($data) || $data instanceof Traversable) {
            if ($data instanceof Params) {
                $data = $data->_data;
            }
            if ($recursive) {
                if ($data instanceof Traversable) {
                    $d = array();
                    foreach ($data as $key => $value) {
                        $d[$key] = $value;
                    }
                    $this->_data = self::mergeArray($this->_data, $d);
                } else {
                    $this->_data = self::mergeArray($this->_data, $data);
                }
            } else {
                foreach ($data as $key => $value) {
                    $this->add($key, $value);
                }
            }
        } elseif ($data !== null) {
            throw new Exception('Postex\Params map data must be an array or an object implementing Traversable.');
        }
    }

    /**
     * @param $a
     * @param $b
     *
     * @return array|mixed
     */
    public static function mergeArray($a, $b)
    {
        $args = func_get_args();
        $res  = array_shift($args);
        while (!empty($args)) {
            $next = array_shift($args);
            foreach ($next as $k => $v) {
                if (is_integer($k)) {
                    isset($res[$k]) ? $res[] = $v : $res[$k] = $v;
                } elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                    $res[$k] = self::mergeArray($res[$k], $v);
                } else {
                    $res[$k] = $v;
                }
            }
        }

        return $res;
    }

    /**
     * Returns whether there is an element at the specified offset.
     * This method is required by the interface ArrayAccess.
     *
     * @param mixed $offset the offset to check on
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->contains($offset);
    }

    /**
     * Returns the element at the specified offset.
     * This method is required by the interface ArrayAccess.
     *
     * @param integer $offset the offset to retrieve element.
     *
     * @return mixed the element at the offset, null if no element is found at the offset
     */
    public function offsetGet($offset)
    {
        return $this->itemAt($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $item
     *
     * @throws Exception
     */
    public function offsetSet($offset, $item)
    {
        $this->add($offset, $item);
    }

    /**
     * @param mixed $offset
     *
     * @throws Exception
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
}