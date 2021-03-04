<?php
declare(strict_types=1);

namespace Ttree\Headless\Types\InputTypes;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use Neos\ContentRepository\Domain\Model\NodeType as CRNodeType;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\ContentRepository\Exception\NodeTypeNotFoundException;
use Neos\Flow\Annotations as Flow;

/**
 * A node represented by its identifier (UUID) or absolute path
 */
class NodeType extends ScalarType
{

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @var string
     */
    public $name = 'NodeTypeInput';

    /**
     * @var string
     */
    public $description = 'A node type represented by its unique name (e.g. "Some.Package:Type")';

    /**
     * Note: The public constructor is needed because the parent constructor is protected, any other way?
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param CRNodeType $value
     * @return string
     */
    public function serialize($value)
    {
        if (!$value instanceof CRNodeType) {
            return null;
        }
        return $value->getName();
    }

    /**
     * @param string $value
     * @return string
     */
    public function parseValue($value)
    {
        if (!is_string($value)) {
            return null;
        }
        try {
            $nodeType = $this->nodeTypeManager->getNodeType($value);
        } catch (NodeTypeNotFoundException $exception) {
            return null;
        }
        return $nodeType;
    }

    /**
     * @param AstNode $valueAST
     * @return string
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        if (!$valueNode instanceof StringValueNode) {
            return null;
        }
        return $this->parseValue($valueNode->value);
    }

}
