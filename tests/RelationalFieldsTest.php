<?php

use Corcel\Acf\Field\PageLink;
use Corcel\Acf\Field\PostObject;
use Corcel\Acf\Field\Term;
use Corcel\Acf\Field\User;
use Corcel\Model\Post;

/**
 * Class RelationalFieldsTests.
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class RelationalFieldsTests extends PHPUnit\Framework\TestCase
{
    /**
     * @var Post
     */
    protected $post;

    protected function setUp(): void
    {
        $this->post = Post::find(56);
    }

    public function testPostObjectField()
    {
        $object = new PostObject($this->post, 'fake_post_object');
        $object->process();
        $this->assertEquals('ACF Basic Fields', $object->get()->post_title);
    }

    public function testPageLinkField()
    {
        $page = new PageLink($this->post, 'fake_page_link');
        $page->process();
        $this->assertEquals('http://wordpress.corcel.dev/acf-content-fields/', $page->get());
    }

    public function testRelationshipField()
    {
        $relation = new PostObject($this->post, 'fake_relationship');
        $relation->process();
        $posts = $relation->get();
        $this->assertEquals([44, 56], $posts->pluck('ID')->toArray());
    }

    public function testTaxonomyField()
    {
        $relation = new Term($this->post, 'fake_taxonomy');
        $relation->process(); // multiple (Collection)
        $this->assertEquals('uncategorized', $relation->get()->first()->slug);

        $relation = new Term($this->post, 'fake_taxonomy_single');
        $relation->process(); // single (Corcel\Term)
        $this->assertEquals('uncategorized', $relation->get()->slug);
    }

    public function testUserField()
    {
        $user = new User($this->post, 'fake_user');
        $user->process();
        $this->assertEquals('admin', $user->get()->user_login);
        $this->assertEquals('admin', $user->get()->nickname);
    }
}
