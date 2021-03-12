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

final class ImagePropertyDefinition
{
    private array $definitions;

    protected function __construct(Type $type, string $propertyName, string $description, ThumbnailService $thumbnailService, ResourceManager $resourceManager)
    {
        $this->definitions = [
            'type' => $type,
            'description' => $description,
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
            'resolve' => function (AccessibleObject $wrappedNode, array $args) use ($propertyName, $thumbnailService, $resourceManager) {
                /** @var CR\NodeInterface $node */
                $node = $wrappedNode->getObject();
                $image = $node->getProperty($propertyName);
                if (!$image instanceof AssetInterface) {
                    return null;
                }
                $args = array_filter($args);
                if ($args !== []) {
                    $configuration = new ThumbnailConfiguration($args['width'] ?? null, $args['maximumWidth'] ?? null, $args['height'] ?? null, $args['maximumHeight'] ?? null, $args['allowCropping'] ?? false, $args['allowUpScaling'] ?? false);
                    $image = $thumbnailService->getThumbnail($image, $configuration);
                }
                $url = $resourceManager->getPublicPersistentResourceUri($image->getResource());
                return new AccessibleObject(new class($image, $url) {
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

    public static function create(Type $type, string $propertyName, string $description, ThumbnailService $thumbnailService, ResourceManager $resourceManager)
    {
        return new static($type, $propertyName, $description, $thumbnailService, $resourceManager);
    }

    public function get()
    {
        return $this->definitions;
    }
}
