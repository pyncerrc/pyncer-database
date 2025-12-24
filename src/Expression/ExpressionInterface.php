<?php
namespace Pyncer\Database\Expression;

use Pyncer\Database\QueryStringInterface;

interface ExpressionInterface extends QueryStringInterface
{
    public function include(string ...$words): static;
    public function exclude(string ...$words): static;
    public function optional(string ...$words): static;
    public function distance(int $distance, string ...$words): static;
    public function negate(string ...$words): static;
}
