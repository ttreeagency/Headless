<?php
declare(strict_types=1);

namespace Ttree\Headless\Types\InputTypes;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\Context as CRContext;
use Ttree\Headless\Types\Scalars\AbsoluteNodePath;
use Ttree\Headless\Types\Scalars\Uuid;

class NodeIdentifierOrPath extends ScalarType
{

    /**
     * @var string
     */
    public $name = 'NodeIdentifierOrPath';

    /**
     * @var string
     */
    public $description = 'A node identifier represented as UUID string';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $value
     * @return string
     */
    public function serialize($value)
    {
        return self::isValid($value) ? $value : null;
    }

    /**
     * @param string $value
     * @return string
     */
    public function parseValue($value)
    {
        return self::isValid($value) ? $value : null;
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

    /**
     * @param string $value
     * @return boolean
     */
    static protected function isValid($value)
    {
        return Uuid::isValid($value) || AbsoluteNodePath::isValid($value);
    }

    /**
     * @param CRContext $context
     * @param string $nodePathOrIdentifier
     * @return NodeInterface
     */
    static public function getNodeFromContext(CRContext $context, $nodePathOrIdentifier)
    {
        $node = Uuid::isValid($nodePathOrIdentifier) ? $context->getNodeByIdentifier($nodePathOrIdentifier) : $context->getNode($nodePathOrIdentifier);
        if ($node === null) {
            throw new \InvalidArgumentException(sprintf('The node "%s" could not be found in the given context', $nodePathOrIdentifier), 1461086543);
        }
        return $node;
    }

}
