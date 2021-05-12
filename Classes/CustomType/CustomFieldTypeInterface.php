<?php
declare(strict_types=1);

namespace Ttree\Headless\CustomType;

use Neos\ContentRepository\Domain\Model\NodeType;
use Wwwision\GraphQL\TypeResolver;

interface CustomFieldTypeInterface
{
    public function type(TypeResolver $typeResolver, NodeType $nodeType);
}
