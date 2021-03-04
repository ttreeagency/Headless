<?php
declare(strict_types=1);

namespace Ttree\Headless\Types\Scalars;

use GraphQL\Language\AST\Node as AstNode;
use GraphQL\Language\AST\StringValue;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use Neos\ContentRepository\Domain\Model\Workspace as NeosWorkspace;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;
use Neos\Flow\Annotations as Flow;

/**
 * Scalar type wrapper for \Neos\ContentRepository\Domain\Model\Workspace values
 */
class Workspace extends ScalarType
{

    /**
     * @Flow\Inject
     * @var WorkspaceRepository
     */
    protected $workspaceRepository;

    /**
     * @var string
     */
    public $name = 'WorkspaceScalar';

    /**
     * @var string
     */
    public $description = 'A workspace, represented by its name';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param NeosWorkspace $value
     * @return string
     */
    public function serialize($value)
    {
        if (!$value instanceof NeosWorkspace) {
            return null;
        }
        return $value->getName();
    }

    /**
     * @param string $value
     * @return NeosWorkspace
     */
    public function parseValue($value)
    {
        if (!is_string($value)) {
            return null;
        }
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->workspaceRepository->findOneByName($value);
    }

    /**
     * @param AstNode $valueNode
     * @return NeosWorkspace
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        if (!$valueNode instanceof StringValueNode) {
            return null;
        }
        return $this->parseValue($valueNode->value);
    }
}
