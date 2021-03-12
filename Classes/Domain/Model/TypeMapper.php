<?php
declare(strict_types=1);

namespace Ttree\Headless\Domain\Model;

use GraphQL\Type\Definition\Type;
use Neos\Media\Domain\Model\Asset;
use Neos\Media\Domain\Model\ImageInterface;
use RuntimeException;
use Ttree\Headless\Types\Image;
use Ttree\Headless\Types\Scalars\DateTime;
use Wwwision\GraphQL\TypeResolver;

final class TypeMapper
{
    protected array $mapping;

    protected string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
        $this->mapping = [];
    }

    public function convert(TypeResolver $typeResolver): Type
    {
        if ($this->mapping === []) {
            $this->mapping = [
                'string' => Type::string(),
                'integer' => Type::int(),
                'boolean' => Type::boolean(),
                'array' => Type::listOf(Type::string()),
                'DateTime' => $typeResolver->get(DateTime::class),
                'Neos\Media\Domain\Model\ImageInterface' => $typeResolver->get(Image::class),
                // @todo add support for the folling types
                'Neos\Media\Domain\Model\Asset' => null,
                'array<Neos\Media\Domain\Model\Asset>' => null,
                'reference' => null,
                'references' => null,
            ];
        }
        if (!isset($this->mapping[$this->type])) {
            throw new RuntimeException(sprintf('Invalid type (%s)', $this->type));
        }

        return $this->mapping[$this->type];
    }
}
