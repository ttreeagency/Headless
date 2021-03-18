<?php
declare(strict_types=1);

namespace Ttree\Headless\Types\RootTypes;

use GraphQL\Type\Definition\ObjectType;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Core\Bootstrap;
use Ttree\Headless\Domain\Generator\ObjectTypeFields;
use Ttree\Headless\Domain\Model\ContentNamespace;
use Ttree\Headless\Domain\Model\QueryableNodeTypes;
use Ttree\Headless\Types\TypeResolverBasedInterface;
use Wwwision\GraphQL\TypeResolver;
use Neos\Flow\Annotations as Flow;

class Query extends ObjectType implements TypeResolverBasedInterface
{
    /**
     * @var array
     * @Flow\InjectConfiguration(package="Ttree.Headless", path="query")
     */
    protected array $configuration = [];

    public function __construct(TypeResolver $typeResolver)
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = Bootstrap::$staticObjectManager->get(ConfigurationManager::class);
        $this->configuration = $configurationManager->getConfiguration(
                ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
                'Ttree.Headless.query'
            ) ?? [];

        $queryableNodeTypes = new QueryableNodeTypes();

        $fields = [];
        /** @var NodeType $nodeType */
        foreach ($queryableNodeTypes->iterate() as $nodeType) {
            $fields = array_merge((new ObjectTypeFields($typeResolver, ContentNamespace::createFromNodeType($nodeType)))->definition(), $fields);
        }

        parent::__construct([
            'name' => $this->configuration['name'],
            'description' => $this->configuration['description'],
            'fields' => $fields
        ]);
    }
}
