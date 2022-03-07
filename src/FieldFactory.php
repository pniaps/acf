<?php

namespace Corcel\Acf;

use Corcel\Acf\Field\Boolean;
use Corcel\Acf\Field\DateTime;
use Corcel\Acf\Field\File;
use Corcel\Acf\Field\FlexibleContent;
use Corcel\Acf\Field\Gallery;
use Corcel\Acf\Field\Image;
use Corcel\Acf\Field\PageLink;
use Corcel\Acf\Field\PostObject;
use Corcel\Acf\Field\Repeater;
use Corcel\Acf\Field\Select;
use Corcel\Acf\Field\Term;
use Corcel\Acf\Field\Text;
use Corcel\Acf\Field\User;
use Corcel\Model\Post;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Class FieldFactory.
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class FieldFactory
{
    private function __construct()
    {
    }

    /**
     * @param string $name
     * @param Post $post
     * @param null|string $type
     *
     * @return FieldInterface|Collection|string
     */
    public static function make($name, Post $post, $type = null)
    {
        if (null === $type) {
            $key = $post->meta->{'_' . $name};
            if ($key === null) { // Field does not exist
                throw new InvalidArgumentException('Field ['.$name.'] not found in post ['.$post->post_title.']');
            }

            $type = static::fetchFieldType($post, $key);
        }


        switch ($type) {
            case 'text':
            case 'textarea':
            case 'number':
            case 'email':
            case 'url':
            case 'link':
            case 'password':
            case 'wysiwyg':
            case 'editor':
            case 'oembed':
            case 'embed':
            case 'color_picker':
            case 'select':
            case 'checkbox':
            case 'radio':
                $field = new Text($post, $name);
                break;
            case 'image':
            case 'img':
                $field = new Image($post, $name);
                break;
            case 'file':
                $field = new File($post, $name);
                break;
            case 'gallery':
                $field = new Gallery($post, $name);
                break;
            case 'true_false':
            case 'boolean':
                $field = new Boolean($post, $name);
                break;
            case 'post_object':
            case 'post':
            case 'relationship':
                $field = new PostObject($post, $name);
                break;
            case 'page_link':
                $field = new PageLink($post, $name);
                break;
            case 'taxonomy':
            case 'term':
                $field = new Term($post, $name);
                break;
            case 'user':
                $field = new User($post, $name);
                break;
            case 'date_picker':
            case 'date_time_picker':
            case 'time_picker':
                $field = new DateTime($post, $name);
                break;
            case 'repeater':
                $field = new Repeater($post, $name);
                break;
            case 'flexible_content':
                $field = new FlexibleContent($post, $name);
                break;
            default:
                throw new InvalidArgumentException('The field type ['.$type.'] is invalid');
        }

        $field->process();

        $field->setFieldType($type);

        return $field;
    }

    /**
     * @param string $fieldKey
     *
     * @return string|null
     */
    public static function fetchFieldType(Post $post, string $fieldKey)
    {
        $post = Post::on($post->getConnectionName())
            ->without('meta')
            ->orWhere(function ($query) use ($fieldKey) {
                $query->where('post_name', $fieldKey);
                $query->where('post_type', 'acf-field');
            })->first();

        if ($post) {
            $fieldData = unserialize($post->post_content);
            $type = isset($fieldData['type']) ? $fieldData['type'] : 'text';

            return $type;
        }

        return null;
    }
}
