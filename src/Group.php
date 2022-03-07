<?php

namespace Corcel\Acf;

use Corcel\Model\Post;
use ErrorException;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * ACF Group Class
 */
class Group extends Post
{
    protected $postType = 'acf-field-group';

    protected $with = [];

    protected $fields = null;

    /**
     * Returns all the fields for the group
     *
     * @return array
     */
    public function fields()
    {
        if(is_null($this->fields)){
            $this->fields = [];
            Post::on($this->getConnectionName())
                ->without('meta')
                ->orWhere(function ($query) {
                    $query->where('post_parent', $this->ID);
                    $query->where('post_type', 'acf-field');
                })->each(function ($post){
                    $fieldData                   = unserialize($post->post_content);
                    $type                        = isset($fieldData['type']) ? $fieldData['type'] : 'text';
                    $this->fields[$post->post_excerpt] = [
                        'type' => $type,
                        'title' => $post->post_title,
                        'name' => $post->post_excerpt,
                        'field' => $post->post_name,
                        'definition' => $fieldData
                    ];
                });

        }
        return $this->fields;
    }

    /**
     * Returns the $post ACF fields for this group
     *
     * @param Post $post
     *
     * @return array
     */
    public function getPostFields(Post $post)
    {
        $fields = [];
        foreach ($this->fields() as $field) {
            $fields[$field['name']] = FieldFactory::make($field['name'], $post, $field['type'])->get();
        }
        return $fields;
    }

    /**
     * Updates or creates the ACF fields of this group for the post
     *
     * @param Post  $post   The post to save custom fields
     * @param array $fields Array of fields values, keyed by field name
     *
     * @return void
     */
    public function savePostFields(Post $post, array $fields)
    {
        if (!$post->exists) {
            throw new ErrorException("Can't save ACF fields. Please, save post first.");
        }
        $groupFields = $this->fields();
        foreach ($fields as $name => $value) {
            if (!isset($groupFields[$name])) {
                throw new InvalidArgumentException('ACF group [' . $this->post_title . '] does not have field [' . $name . ']');
            }
            $field = $groupFields[$name];

            if ($field['type'] == 'date_picker' && Carbon::canBeCreatedFromFormat($value, 'Y-m-d')) {
                $value = Carbon::createFromFormat('Y-m-d', $value)->format('Ymd');
            }

            if ($field['type'] == 'date_time_picker') {
                $value = Carbon::parse($value)->toDateTimeString();
            }

            $this->createOrUpdatePostMeta($post, $name, $value);
            $this->createOrUpdatePostMeta($post, '_' . $name, $field['field']);
        }
    }

    /**
     * Creates or updates the post meta for the ACF fields
     *
     * @param Post   $post  Post to save custom field
     * @param string $key   The custom field name
     * @param mixed  $value The custom field value
     *
     * @return void
     */
    private function createOrUpdatePostMeta(Post $post, $key, $value)
    {
        $meta = $post->meta->first(function ($meta) use ($key) {
            return $meta->meta_key === $key;
        });
        if ($meta) {
            $meta->update(['meta_value' => $value]);
        } else {
            $post->meta()->create([
                'meta_key' => $key,
                'meta_value' => $value,
            ]);
        }
    }
}
