<?php
declare(strict_types=1);

namespace Ttree\Headless\Domain\Model;

use GraphQL\Type\Definition\Type;
use Ttree\Headless\Types\Image;
use Ttree\Headless\Types\Scalars\DateTime;
use Wwwision\GraphQL\TypeResolver;

final class TypeMapper
{
    protected $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function convert(TypeResolver $typeResolver): ?Type
    {
        $mapping = [
            'string' => Type::string(),
            'integer' => Type::int(),
            'boolean' => Type::boolean(),
            'array' => Type::listOf(Type::string()),
            'DateTime' => $typeResolver->get(DateTime::class),
            'Neos\Media\Domain\Model\ImageInterface' => $typeResolver->get(Image::class),
            'Neos\Media\Domain\Model\Asset' => null,
            'array<Neos\Media\Domain\Model\Asset>' => null,
            'reference' => null,
            'references' => null,
        ];
        return $mapping[$this->type] ?? null;
    }
}
