<?php
declare(strict_types=1);

namespace Ttree\Headless\CustomType;

use Neos\ContentRepository\Domain\Model\NodeType;
use Wwwision\GraphQL\TypeResolver;

interface CustomFieldInterface
{
    public function args(TypeResolver $typeResolver): array;
    public function description(NodeType $nodeType): string;
    public function resolve(NodeType $nodeType): \Closure;
}
