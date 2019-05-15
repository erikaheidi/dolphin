<?php
/**
 * "Magic" Class to hold Droplet Json values
 */

namespace Dolphin;


class Droplet
{
    /** @var array droplet info obtained with API call */
    protected $droplet_values;

    /**
     * Droplet constructor.
     * @param array $droplet_values
     */
    function __construct(array $droplet_values)
    {
        $this->droplet_values = $droplet_values;
    }

    /**
     * @param string $name Key
     * @param string $value Value
     */
    function __set($name, $value)
    {
        $this->droplet_values[$name] = $value;
    }

    /**
     * @param string $name
     * @return string|null
     */
    function __get($name)
    {
        return isset($this->droplet_values[$name]) ? $this->droplet_values[$name] : null;
    }
}