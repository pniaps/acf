<?php

namespace Corcel\Acf\Field;

/**
 * Class Boolean.
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class Boolean extends Text
{
    /**
     * @return bool
     */
    public function get()
    {
        return (bool) parent::get();
    }
}
