<?php
declare(strict_types=1);

namespace Ttree\Headless;

use Neos\ContentRepository\Domain\Model\NodeType;
use Wwwision\GraphQL\TypeResolver;

interface CustomTypeInterface
{
    public static function supportedNodeTypes(): array;
    public function args(TypeResolver $typeResolver): array;
    public function description(): string;
    public function resolve(NodeType $nodeType): \Closure;
}
