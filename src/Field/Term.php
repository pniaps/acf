<?php

namespace Corcel\Acf\Field;

use Corcel\Model\Post;
use Illuminate\Support\Collection;

/**
 * Class Term.
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class Term extends BasicField
{
    /**
     * @var mixed
     */
    protected $items;

    /**
     * @var \Corcel\Model\Term
     */
    protected $term;

    /**
     * @param Post $post
     */
    public function __construct(Post $post, string $name)
    {
        parent::__construct($post, $name);
        $this->term = new \Corcel\Model\Term();
        $this->term->setConnection($post->getConnectionName());
    }

    /**
     * @param string $fieldName
     */
    public function process()
    {
        $value = $this->fetchValue();
        if (is_array($value)) {
            $this->items = $this->term->whereIn('term_id', $value)->get(); // ids
        } else {
            $this->items = $this->term->find(intval($value));
        }
    }

    /**
     * @return Term|Collection
     */
    public function get()
    {
        return $this->items; // Collection or Term object
    }
}
