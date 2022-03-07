<?php

use Corcel\Acf\Field\Text;
use Corcel\Model\Post;

/**
 * Class BasicFieldTest.
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class EmptyBasicFieldsTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var Post
     */
    protected $post;

    /**
     * Setup a base $this->post object to represent the page with the basic fields.
     */
    protected function setUp(): void
    {
        $this->post = Post::find(91); // it' a page with empty custom fields
    }

    public function testTextFieldValue()
    {
        $text = new Text($this->post, 'fake_text');
        $text->process();

        $this->assertSame('', $text->get());
    }

    public function testTextareaFieldValue()
    {
        $textarea = new Text($this->post, 'fake_textarea');
        $textarea->process();

        $this->assertSame('', $textarea->get());
    }

    public function testNumberFieldValue()
    {
        $number = new Text($this->post, 'fake_number');
        $number->process();

        $this->assertSame('', $number->get());
    }

    public function testEmailFieldValue()
    {
        $email = new Text($this->post, 'fake_email');
        $email->process();

        $this->assertSame('', $email->get());
    }

    public function testUrlFieldValue()
    {
        $url = new Text($this->post, 'fake_url');
        $url->process();

        $this->assertSame('', $url->get());
    }

    public function testPasswordFieldValue()
    {
        $password = new Text($this->post, 'fake_password');
        $password->process();

        $this->assertSame('', $password->get());
    }
}
