<?php

use Corcel\Acf\Field\Boolean;
use Corcel\Acf\Field\Text;
use Corcel\Model\Post;

/**
 * Class ChoicesFieldsTest.
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class ChoicesFieldsTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var Post
     */
    protected $post;

    protected function setUp(): void
    {
        $this->post = Post::find(44);
    }

    public function testSelectField()
    {
        $select = new Text($this->post,'fake_select');
        $select->process();
        $this->assertEquals('red', $select->get());
    }

    public function testSelectMultipleField()
    {
        $select = new Text($this->post, 'fake_select_multiple');
        $select->process();
        $this->assertEquals(['yellow', 'green'], $select->get());
    }

    public function testCheckboxField()
    {
        $check = new Text($this->post, 'fake_checkbox');
        $check->process();
        $this->assertEquals(['blue', 'yellow'], $check->get());
    }

    public function testRadioField()
    {
        $radio = new Text($this->post, 'fake_radio_button');
        $radio->process();
        $this->assertEquals('green', $radio->get());
    }

    public function testTrueFalseField()
    {
        $boolean = new Boolean($this->post, 'fake_true_false');
        $boolean->process();
        $this->assertTrue($boolean->get());
    }
}
