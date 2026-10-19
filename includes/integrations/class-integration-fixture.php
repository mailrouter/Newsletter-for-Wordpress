<?php

/**
 * Class NL4WP_Integration_Fixture
 *
 * @since 3.0
 * @method is_installed()
 * @property string $name
 * @property string $description
 */
class NL4WP_Integration_Fixture
{
    /**
     * @var string
     */
    public $slug;

    /**
     * @var string
     */
    public $class;

    /**
     * @var bool
     */
    public $enabled;

    /**
     * @var bool
     */
    public $enabled_by_default;

    /**
     * @var NL4WP_Integration
     */
    public $instance;

    /**
     * @var array
     */
    public $options;

    /**
     * @param string $slug
     * @param string $class
     * @param bool $enabled_by_default
     * @param array $options
     */
    public function __construct($slug, $class, $enabled_by_default, array $options)
    {
        $this->slug               = $slug;
        $this->class              = $class;
        $this->enabled_by_default = $enabled_by_default;
        $this->enabled            = $enabled_by_default;
        $this->options            = $options;

        if (! empty($options['enabled'])) {
            $this->enabled = true;
        }
    }

    /**
     * Returns the actual instance
     *
     * @return NL4WP_Integration
     */
    public function load()
    {
        if (! $this->instance instanceof NL4WP_Integration) {
            $this->instance = new $this->class($this->slug, $this->options);
        }

        return $this->instance;
    }

    /**
     * Tunnel everything to NL4WP_Integration class
     *
     * @param string $name
     * @param array $arguments
     *
     * @return NL4WP_Integration
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([ $this->load(), $name ], $arguments);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function __get($name)
    {
        return $this->load()->$name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->slug;
    }
}
