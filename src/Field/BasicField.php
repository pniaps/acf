<?php

namespace Corcel\Acf\Field;

use Corcel\Model;
use Corcel\Model\Post;

/**
 * Class BasicField.
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
abstract class BasicField
{
    /**
     * @var Model
     */
    protected $post;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var string
     */
    protected $connection;

    /**
     * Constructor method.
     *
     * @param Post $post
     */
    public function __construct(Model $post)
    {
        $this->post = $post;
    }

    /**
     * Get the value of a field according it's post ID.
     *
     * @param string $field
     *
     * @return array|string
     */
    public function fetchValue($field)
    {
        $value = $this->post->meta->$field;

        if (isset($value) and ! is_null($value)) {
            if ($array = @unserialize($value) and is_array($array)) {
                $this->value = $array;

                return $array;
            } else {
                $this->value = $value;

                return $value;
            }
        }
    }

    /**
     * @param string $fieldName
     *
     * @return string
     */
    public function fetchFieldKey($fieldName)
    {
        $this->name = $fieldName;

        $this->key = $this->post->meta->{'_' . $fieldName};

        return $this->key;
    }

    /**
     * @param string $fieldKey
     *
     * @return string|null
     */
    public function fetchFieldType($fieldKey)
    {
        $post = Post::on($this->post->getConnectionName())
            ->without('meta')
            ->orWhere(function ($query) use ($fieldKey) {
                $query->where('post_name', $fieldKey);
                $query->where('post_type', 'acf-field');
            })->first();

        if ($post) {
            $fieldData = unserialize($post->post_content);
            $this->type = isset($fieldData['type']) ? $fieldData['type'] : 'text';

            return $this->type;
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->get();
    }
}
