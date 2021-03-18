<?php
declare(strict_types=1);

namespace Ttree\Headless\Domain\Model;

use GraphQL\Type\Definition\Type;
use Neos\Eel\EelEvaluatorInterface;
use Neos\Eel\Utility;
use Wwwision\GraphQL\AccessibleObject;
use Neos\ContentRepository\Domain\Model as CR;
use Neos\Flow\Annotations as Flow;

final class SimplePropertyDefinition
{
    /**
     * @Flow\Inject(lazy=false)
     * @var EelEvaluatorInterface
     */
    protected $eelEvaluator;

    /**
     * @Flow\InjectConfiguration(package="Ttree.Headless", path="eel.defaultContext")
     * @var array
     */
    protected $defaultContextConfiguration;

    private array $definitions;

    protected function __construct(Type $type, string $propertyName, string $description)
    {
        $this->definitions = [
            'type' => $type,
            'description' => $description,
            'resolve' => function (AccessibleObject $wrappedNode) use ($propertyName) {
                /** @var CR\NodeInterface $node */
                $node = $wrappedNode->getObject();
                $expression = $node->getNodeType()->getConfiguration('properties.' . $propertyName . '.options.Ttree:Headless.getter');
                if ($expression && is_string($expression)) {
                    $value = $this->getValueByExpression($node, $expression);
                } else {
                    $value = $this->getValue($node, $propertyName);
                }
                $postprocessors = $node->getNodeType()->getConfiguration('properties.' . $propertyName . '.options.Ttree:Headless.processors') ?? [];
                foreach ($postprocessors as $postprocessor) {
                    if (!$postprocessor || !is_string($postprocessor)) continue;
                    $value = $this->applyPostProcessor($node, $value, $postprocessor);
                }
                return $value;
            }
        ];
    }

    public static function create(Type $type, string $propertyName, string $description)
    {
        return new static($type, $propertyName, $description);
    }

    public function get()
    {
        return $this->definitions;
    }

    public function getValue(CR\NodeInterface $node, string $propertyName)
    {
        return $node->getProperty($propertyName);
    }

    public function getValueByExpression(CR\NodeInterface $node, string $expression)
    {
        return Utility::evaluateEelExpression($expression, $this->eelEvaluator, ['node' => $node], $this->defaultContextConfiguration);
    }

    public function applyPostProcessor(CR\NodeInterface $node, $value, string $expression)
    {
        return Utility::evaluateEelExpression($expression, $this->eelEvaluator, ['node' => $node, 'value' => $value], $this->defaultContextConfiguration);
    }
}
