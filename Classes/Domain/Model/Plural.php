<?php
declare(strict_types=1);

namespace Ttree\Headless\Domain\Model;

final class Plural
{
    /**
     * @var string
     */
    protected $singular;

    /**
     * @var string
     */
    protected $plural;

    public function __construct(string $singular)
    {
        $this->singular = $singular;
        $lastLetter = strtolower($singular[strlen($singular) - 1]);
        switch ($lastLetter) {
            case 'y':
                $this->plural = substr($singular, 0, -1) . 'ies';
                break;
            case 's':
                $this->plural = $singular . 'es';
                break;
            default:
                $this->plural = $singular . 's';
        }
    }

    public function getPlural(): string
    {
        return $this->plural;
    }

    public function __toString(): string
    {
        return $this->plural;
    }
}
