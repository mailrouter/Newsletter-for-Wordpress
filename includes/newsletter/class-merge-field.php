<?php

/**
 * Represents a Merge Field in Newsletter
 *
 * @since 4.0
 * @access public
 */
class NL4WP_Newsletter_Merge_Field
{

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $field_type;

    /**
     * @var string
     */
    public $tag;

    /**
     * @var bool Is this a required field for the list it belongs to?
     */
    public $required = false;

    /**
     * @var array
     */
    public $choices = array();

    /**
     * @var bool Is this field public? As in, should it show on forms?
     */
    public $public = true;

    /**
     * @var string Default value for the field.
     */
    public $default_value = '';

    /**
     * @var string The field format (eg phone format or date format)
     */
    public $format = '';

    /**
     * @param string $name
     * @param string $field_type
     * @param string $tag
     * @param bool $required
     * @param array $choices
     */
    public function __construct($name, $field_type, $tag, $required = false, $choices = array())
    {
        $this->name = $name;
        $this->field_type = $field_type;
        $this->tag = strtoupper($tag);
        $this->required = $required;
        $this->choices = $choices;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function __get($name)
    {

        // for backwards compatibility with v3.x, channel these properties to their new names
        if ($name === 'default') {
            return $this->default_value;
        }
    }

    /**
     * Creates our local object from Newsletter API data.
     *
     * @param object $data
     *
     * @return NL4WP_Newsletter_Merge_Field
     */
    public static function from_data($data)
    {
        $instance = new self($data->name, $data->type, $data->tag, $data->required);

        if (! empty($data->options->choices)) {
            $instance->choices = $data->options->choices;
        }

        if (! empty($data->options->date_format)) {
            $instance->format = $data->options->date_format;
        } elseif( !empty($data->options->phone_format)) {
            $instance->format = $data->options->phone_format;
        }

        $optional = array(
            'public',
            'default_value'
        );

        foreach ($optional as $key) {
            if (isset($data->$key)) {
                $instance->$key = $data->$key;
            }
        }

        return $instance;
    }
}
