<?php

namespace Corcel\Acf\Field;

use Corcel\Acf\FieldFactory;
use Corcel\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use stdClass;

/**
 * Class Flexible Content.
 *
 * @author Marco Boom <info@marcoboom.nl>
 */
class FlexibleContent extends BasicField
{
    /**
     * @var Collection
     */
    protected $fields;

    /**
     * @param string $fieldName
     */
    public function process()
    {
        $builder = $this->fetchPostsMeta($this->name, $this->post);
        $fields = $this->fetchFields($this->name, $builder);

        $this->fields = new Collection($fields);
    }

    /**
     * @return Collection
     */
    public function get()
    {
        return $this->fields;
    }

    /**
     * @param string $metaKey
     * @param string $fieldName
     *
     * @return int
     */
    protected function retrieveIdFromFieldName($metaKey, $fieldName)
    {
        return (int) str_replace("{$fieldName}_", '', $metaKey);
    }

    /**
     * @param string $metaKey
     * @param string $fieldName
     * @param int    $id
     *
     * @return string
     */
    protected function retrieveFieldName($metaKey, $fieldName, $id)
    {
        $pattern = "{$fieldName}_{$id}_";

        return str_replace($pattern, '', $metaKey);
    }

    /**
     * @param $fieldName
     * @param Post $post
     *
     * @return mixed
     */
    protected function fetchPostsMeta($fieldName, Model $post)
    {
        $builder = $post->meta()->where('meta_key', 'like', "{$fieldName}_%");

        return $builder;
    }

    /**
     * @param $fieldName
     * @param $builder
     *
     * @return mixed
     */
    protected function fetchFields($fieldName, Builder $builder)
    {
        $fields = [];
        $blocks  = $this->fetchValue($fieldName, $this->post);

        foreach ($builder->get() as $meta) {
            $id = $this->retrieveIdFromFieldName($meta->meta_key, $fieldName);

            $name = $this->retrieveFieldName($meta->meta_key, $fieldName, $id);

            $post = $this->post->ID != $meta->post_id ? $this->post->find($meta->post_id) : $this->post;
            $field = FieldFactory::make($meta->meta_key, $post);

            if ($field === null || !array_key_exists($id, $blocks)) {
                continue;
            }

            if (empty($fields[$id])) {
                $fields[$id] = new stdClass;
                $fields[$id]->type = $blocks[$id];
                $fields[$id]->fields =  new stdClass;
            }

            $fields[$id]->fields->$name = $field->get();
        }

        ksort($fields);

        return $fields;
    }
}
