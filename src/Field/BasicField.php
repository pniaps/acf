<?php

namespace Corcel\Acf\Field;

use Corcel\Acf\FieldInterface;
use Corcel\Model\Post;

/**
 * Class BasicField.
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
abstract class BasicField implements FieldInterface
{
    /**
     * @var Post
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
    public function __construct(Post $post, string $name)
    {
        $this->post = $post;

        $this->name = $name;

        $this->key = $this->post->meta->{'_' . $this->name};
    }

    /**
     * Get the value of a field according it's post ID.
     *
     * @param string $field
     *
     * @return array|string
     */
    public function fetchValue()
    {
        $value = $this->post->meta->{$this->name};

        if (isset($value) and ! is_null($value)) {
            if ($array = @unserialize($value) and is_array($array)) {
                $this->value = $array;
            } else {
                $this->value = $value;
            }
        }
        return $this->value;
    }

    /**
     * Sets the field type
     * @param $type
     *
     * @return static
     */
    public function setFieldType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->get();
    }
}
