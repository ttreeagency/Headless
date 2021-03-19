<?php
declare(strict_types=1);

namespace Ttree\Headless\Domain\Model;

use GraphQL\Type\Definition\Type;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Media\Domain\Model\ThumbnailConfiguration;
use Neos\Media\Domain\Service\ThumbnailService;
use Wwwision\GraphQL\AccessibleObject;
use Neos\ContentRepository\Domain\Model as CR;
use Neos\Flow\Annotations as Flow;

final class ImagePropertyDefinition
{
    /**
     * @var ThumbnailService
     * @Flow\Inject
     */
    protected $thumbnailService;

    /**
     * @var ResourceManager
     * @Flow\Inject
     */
    protected $resourceManager;

    private Type $type;
    private string $propertyName;
    private string $description;
    private array $definitions;

    protected function __construct(Type $type, string $propertyName, string $description)
    {
        $this->definitions = [];
        $this->type = $type;
        $this->propertyName = $propertyName;
        $this->description = $description;
    }

    public static function create(Type $type, string $propertyName, string $description)
    {
        return new static($type, $propertyName, $description);
    }

    public function get()
    {
        if ($this->definitions === []) {
            $this->definitions = [
                'type' => $this->type,
                'description' => $this->description,
                'args' => [
                    'width' => ['type' => Type::int(), 'description' => 'Desired width of the image'],
                    'maximumWidth' => ['type' => Type::int(), 'description' => 'Desired maximum width of the image'],
                    'height' => ['type' => Type::int(), 'description' => 'Desired height of the image'],
                    'maximumHeight' => ['type' => Type::int(), 'description' => 'Desired maximum height of the image'],
                    'allowCropping' => [
                        'type' => Type::boolean(),
                        'defaultValue' => false,
                        'description' => 'Whether the image should be cropped if the given sizes would hurt the aspect ratio'
                    ],
                    'allowUpScaling' => [
                        'type' => Type::boolean(),
                        'defaultValue' => false,
                        'description' => 'Whether the resulting image size might exceed the size of the original image'
                    ],
                ],
                'resolve' => function (AccessibleObject $wrappedNode, array $args) {
                    /** @var CR\NodeInterface $node */
                    $node = $wrappedNode->getObject();
                    $asset = $node->getProperty($this->propertyName);
                    if (!$asset instanceof AssetInterface) {
                        return null;
                    }
                    $args = array_filter($args);
                    if ($args !== []) {
                        $configuration = new ThumbnailConfiguration($args['width'] ?? null, $args['maximumWidth'] ?? null, $args['height'] ?? null, $args['maximumHeight'] ?? null, $args['allowCropping'] ?? false, $args['allowUpScaling'] ?? false);
                        $asset = $this->thumbnailService->getThumbnail($asset, $configuration);
                    }
                    $url = $this->resourceManager->getPublicPersistentResourceUri($asset->getResource());
                    return new AccessibleObject(new class($asset, $url) {
                        public int $width;
                        public int $height;
                        public string $url;
                        public function __construct(ImageInterface $image, string $url)
                        {
                            $this->width = $image->getWidth();
                            $this->height = $image->getHeight();
                            $this->url = $url;
                        }
                    });
                }
            ];
        }
        return $this->definitions;
    }
}
