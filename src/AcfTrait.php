<?php

namespace Corcel\Acf;

use ErrorException;
use InvalidArgumentException;

trait AcfTrait
{
    /**
     * The id of the group to load automatically
     *
     * @var bool
     */
    // public $acfgroup = false;

    private $acf_fields = null;

    public function group()
    {
        if (is_int($this->acfgroup)) {
            $this->acfgroup = Group::find($this->acfgroup);
        }
        return $this->acfgroup;
    }

    public function getAcfField($name = null)
    {
        if (is_null($this->acf_fields)) {
            $this->acf_fields = [];
            foreach ($this->group()->fields() as $field) {
                $this->acf_fields[$field['name']] = FieldFactory::make($field['name'], $this, $field['type']);
            }
        }
        if ($name && $this->acf_fields && $this->acf_fields[$name]) {
            return $this->acf_fields[$name]->get();
        }
        return null;
    }

    /**
     * Updates or creates the ACF fields of this group for the post
     *
     * @param array $fields Array of fields values, keyed by field name
     *
     * @return void
     */
    public function saveAcfFields(array $fields)
    {
        if (!$this->exists) {
            throw new ErrorException("Can't save ACF fields. Please, save post first.");
        }
        if (is_null($this->acf_fields)) {
            $this->acf_fields = [];
            foreach ($this->group()->fields() as $field) {
                $this->acf_fields[$field['name']] = FieldFactory::make($field['name'], $this, $field['type']);
            }
        }
        foreach ($fields as $name => $value) {
            if (!isset($this->acf_fields[$name])) {
                throw new InvalidArgumentException('ACF group [' . $this->post_title . '] does not have field [' . $name . ']');
            }
            $this->acf_fields[$name]->update($value);
        }
    }
}
