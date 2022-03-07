<?php

namespace Corcel\Acf\Field;

use Corcel\Model\Meta\PostMeta;
use Corcel\Model\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Class Image.
 *
 * @author Junior Grossi
 */
class Image extends BasicField
{
    /**
     * @var int
     */
    public $width;

    /**
     * @var int
     */
    public $height;

    /**
     * @var string
     */
    public $filename;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $mime_type;

    /**
     * @var array
     */
    protected $sizes = [];

    /**
     * @var bool
     */
    protected $loadFromPost = false;

    /**
     * @param string $field
     */
    public function process()
    {
        $attachmentId = $this->fetchValue();

        $connection = $this->post->getConnectionName();

        if ($attachment = Post::on($connection)->find(intval($attachmentId))) {
            $this->fillFields($attachment);

            $imageData = $this->fetchMetadataValue($attachment);

            $this->fillMetadataFields($imageData);
        }
    }

    /**
     * @return Image
     */
    public function get()
    {
        return $this;
    }

    /**
     * @param Post $attachment
     */
    protected function fillFields(Post $attachment)
    {
        $this->attachment = $attachment;

        $this->mime_type = $attachment->post_mime_type;
        $this->url = $attachment->guid;
        $this->description = $attachment->post_excerpt;
    }

    /**
     * @param string $size
     * @param bool $useOriginalFallback
     *
     * @return Image
     */
    public function size($size, $useOriginalFallback = false)
    {
        if (isset($this->sizes[$size])) {
            return $this->fillThumbnailFields($this->sizes[$size]);
        }

        return $useOriginalFallback ? $this : $this->fillThumbnailFields($this->sizes['thumbnail']);
    }

    /**
     * @param array $data
     *
     * @return Image
     */
    protected function fillThumbnailFields(array $data)
    {
        $size = new static($this->post, '');
        $size->filename = $data['file'];
        $size->width = $data['width'];
        $size->height = $data['height'];
        $size->mime_type = $data['mime-type'];

        $urlPath = dirname($this->url);
        $size->url = sprintf('%s/%s', $urlPath, $size->filename);

        return $size;
    }

    /**
     * @param Post $attachment
     *
     * @return array
     */
    protected function fetchMetadataValue(Post $attachment)
    {
        $meta = $attachment->meta->{'_wp_attachment_metadata'};
        return unserialize($meta);
    }

    /**
     * @param Collection $attachments
     *
     * @return Collection|array
     */
    protected function fetchMultipleMetadataValues(Collection $attachments)
    {
        $ids = $attachments->pluck('ID')->toArray();
        $metadataValues = [];

        $metaRows = PostMeta::whereIn("post_id", $ids)
            ->where('meta_key', '_wp_attachment_metadata')
            ->get();

        foreach ($metaRows as $meta) {
            $metadataValues[$meta->post_id] = unserialize($meta->meta_value);
        }

        return $metadataValues;
    }

    /**
     * @param array $imageData
     */
    protected function fillMetadataFields(array $imageData)
    {
        $this->filename = basename($imageData['file']);
        $this->width = $imageData['width'];
        $this->height = $imageData['height'];
        $this->sizes = $imageData['sizes'];
    }

    public function update($value)
    {
        if (Str::startsWith($value, 'data:image'))
        {
            // 0. Make the image
            $image = \Intervention\Image\Facades\Image::make($value)->encode('jpg', 90);

            // 1. Generate a filename.
            $name = md5($value.time()).'.jpg';
            $path = storage_path('temp/'.$name);

            // 2. Store the image on disk.
            $image->save($path);

            // 3. Upload to wordpress
            $response = Http::attach('file', file_get_contents($path), $name)
                ->withBasicAuth(env('WORDPRESS_API_USERNAME'),env('WORDPRESS_API_PASSWORD'))
                ->post(env('WORDPRESS_API_ENDPOINT'));

            // 4. Delete local image after uploaded to wordpress
            File::delete($path);

            // 5. Delete old image from wordpress
            Http::withBasicAuth(env('WORDPRESS_API_USERNAME'),env('WORDPRESS_API_PASSWORD'))
                ->delete(env('WORDPRESS_API_ENDPOINT').'/'.$this->value, ['force' => true]);

            $value = $response->json('id');
        }
        parent::update($value);
    }
}
