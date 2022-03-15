<?php

/**
* Class NL4WP_Field_Map
*
* @access private
* @since 4.0
* @ignore
*/
class NL4WP_List_Data_Mapper
{

    /**
    * @var array
    */
    private $data = array();

    /**
    * @var array
    */
    private $list_ids = array();

    /**
    * @var NL4WP_Field_Formatter
    */
    private $formatter;

    /**
    * @param array $data
    * @param array $list_ids
    */
    public function __construct(array $data, array $list_ids)
    {
        $this->data = array_change_key_case($data, CASE_UPPER);
        $this->list_ids = $list_ids;
        $this->formatter = new NL4WP_Field_Formatter();

        if (! isset($this->data['EMAIL'])) {
            throw new InvalidArgumentException('Data needs at least an EMAIL key.');
        }
    }

    /**
    * @return NL4WP_Newsletter_Subscriber[]
    */
    public function map()
    {
        $newsletter = new NL4WP_Newsletter();
        $map = array();

        foreach ($this->list_ids as $list_id) {
            $list = $newsletter->get_list($list_id, true);

            if ($list instanceof NL4WP_Newsletter_List) {
                $map[ $list_id ] = $this->map_list($list);
            }
        }

        return $map;
    }

    /**
    * @param NL4WP_Newsletter_List $list
    *
    * @return NL4WP_Newsletter_Subscriber
    */
    protected function map_list(NL4WP_Newsletter_List $list)
    {
        $subscriber = new NL4WP_Newsletter_Subscriber();
        $subscriber->email_address = $this->data['EMAIL'];

        // find merge fields
        foreach ($list->merge_fields as $merge_field) {

            // skip EMAIL field as that is handled separately (see above)
            if ($merge_field->tag === 'EMAIL') {
                continue;
            }

            // use empty() here to skip empty field values
            if (empty($this->data[ $merge_field->tag ])) {
                continue;
            }

            // format field value
            $value = $this->data[ $merge_field->tag ];
            $value = $this->format_merge_field_value($value, $merge_field->field_type, $merge_field->format);

            // add to map
            $subscriber->merge_fields[ $merge_field->tag ] = $value;
        }

        // find interest categories
        if (! empty($this->data['INTERESTS'])) {
            foreach ($list->interest_categories as $interest_category) {
                foreach ($interest_category->interests as $interest_id => $interest_name) {
                    // straight lookup by ID as key with value copy.
                    if (isset($this->data['INTERESTS'][ $interest_id ])) {
                        $subscriber->interests[ $interest_id ] = $this->formatter->boolean($this->data['INTERESTS'][ $interest_id ]);
                    }

                    // straight lookup by ID as top-level value
                    if (in_array($interest_id, $this->data['INTERESTS'], false)) {
                        $subscriber->interests[ $interest_id ] = true;
                    }

                    // look in array with category ID as key.
                    if (isset($this->data['INTERESTS'][ $interest_category->id ])) {
                        $value = $this->data['INTERESTS'][ $interest_category->id ];
                        $values = is_array($value) ? $value : array_map('trim', explode('|', $value));

                        // find by category ID + interest ID
                        if (in_array($interest_id, $values, false)) {
                            $subscriber->interests[ $interest_id ] = true;
                        }

                        // find by category ID + interest name
                        if (in_array($interest_name, $values)) {
                            $subscriber->interests[ $interest_id ] = true;
                        }
                    }
                }
            }
        }

        // find language
        /* @see http://kb.newsletter.com/lists/managing-subscribers/view-and-edit-subscriber-languages?utm_source=mc-api&utm_medium=docs&utm_campaign=apidocs&_ga=1.211519638.2083589671.1469697070 */
        if (! empty($this->data['MC_LANGUAGE'])) {
            $subscriber->language = $this->formatter->language($this->data['MC_LANGUAGE']);
        }

        return $subscriber;
    }


    /**
    * @param mixed $field_value
    * @param string $field_type
    * @param string $field_format
     *
    * @return mixed
    */
    private function format_merge_field_value($field_value, $field_type, $field_format = '')
    {
        $field_type = strtolower($field_type);

        if (method_exists($this->formatter, $field_type)) {
            $field_value = call_user_func(array($this->formatter, $field_type), $field_value, $field_format);
        }

        /**
        * Filters the value of a field after it is formatted.
        *
        * Use this to format a field value according to the field type (in Newsletter).
        *
        * @since 3.0
        * @param string $field_value The value
        * @param string $field_type The type of the field (in Newsletter)
        */
        $field_value = apply_filters('nl4wp_format_field_value', $field_value, $field_type);

        return $field_value;
    }
}
