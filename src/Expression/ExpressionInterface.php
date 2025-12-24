<?php
namespace Pyncer\Database\Expression;

use Pyncer\Database\QueryStringInterface;

interface ExpressionInterface extends QueryStringInterface
{
    public function includes(string ...$words): static;
    public function excludes(string ...$words): static;
    public function optional(string ...$words): static;
    public function distance(int $distance, string ...$words): static;
    public function negates(string ...$words): static;
}
