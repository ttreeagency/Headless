<?php
declare(strict_types=1);

namespace Ttree\Headless\Domain\Model;

use GraphQL\Type\Definition\Type;
use Wwwision\GraphQL\AccessibleObject;
use Neos\ContentRepository\Domain\Model as CR;

final class SimplePropertyDefinition
{
    private array $definitions;

    protected function __construct(Type $type, string $propertyName, string $description)
    {
        $this->definitions = [
            'type' => $type,
            'description' => $description,
            'resolve' => function (AccessibleObject $wrappedNode) use ($propertyName) {
                /** @var CR\NodeInterface $node */
                $node = $wrappedNode->getObject();
                return $node->getProperty($propertyName);
            }
        ];
    }

    public static function create(Type $type, string $propertyName, string $description)
    {
        return new static($type, $propertyName, $description);
    }

    public function get()
    {
        return $this->definitions;
    }
}
