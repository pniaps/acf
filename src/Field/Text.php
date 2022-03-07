<?php

namespace Corcel\Acf\Field;

/**
 * Class Text.
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class Text extends BasicField
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @param string $field
     */
    public function process()
    {
        $this->value = $this->fetchValue();
    }

    /**
     * @return string
     */
    public function get()
    {
        return $this->value;
    }
}
