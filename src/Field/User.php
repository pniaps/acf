<?php

namespace Corcel\Acf\Field;

use Corcel\Model\Post;

/**
 * Class User.
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class User extends BasicField
{
    /**
     * @var \Corcel\Model\User
     */
    protected $user;

    /**
     * @var \Corcel\Model\User
     */
    protected $value;

    /**
     * @param Post $post
     */
    public function __construct(Post $post, string $name)
    {
        parent::__construct($post, $name);
        $this->user = new \Corcel\Model\User();
        $this->user->setConnection($post->getConnectionName());
    }

    /**
     * @param string $fieldName
     */
    public function process()
    {
        $userId = $this->fetchValue();
        $this->value = $this->user->find($userId);
    }

    /**
     * @return \Corcel\Model\User
     */
    public function get()
    {
        return $this->value;
    }
}
