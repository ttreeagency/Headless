<?php
declare(strict_types=1);

namespace Ttree\Headless\Service;

use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Flow\Annotations as Flow;
use Ttree\Headless\Types\NodeInterface;
use Ttree\Headless\Types\UnionType;
use Wwwision\GraphQL\TypeResolver;

/**
 * @Flow\Scope("singleton")
 */
final class UnionTypeRegistry
{
    protected $unionTypes = [];

    /**
     * @var array
     * @Flow\InjectConfiguration("unionTypes")
     */
    protected $configuration = [];

    /**
     * @var NodeTypeManager
     * @Flow\Inject
     */
    protected $nodeTypeManager;

    public function get(TypeResolver $typeResolver, string $name): UnionType
    {
        if (!isset($this->configuration[$name])) {
            throw new \InvalidArgumentException(sprintf('Union type not configured, please check your Settings at Ttree.Headless.unionType.%s', $name));
        }
        $name = trim($name);
        if (!isset($this->unionTypes[$name])) {
            $configuration = $this->configuration[$name];
            $configuration['types'] = array_map(function (string $nodeType) {
                return $this->nodeTypeManager->getNodeType($nodeType);
            }, array_keys(array_filter($configuration['types'])));

            $this->unionTypes[$name] = new UnionType($typeResolver, $name, $configuration);
        }
        return $this->unionTypes[$name];
    }
}
