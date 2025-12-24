<?php
namespace Pyncer\Database\Sql\Expression;

use Pyncer\Database\Expression\AbstractExpression;

use function Pyncer\Array\unset_empty as pyncer_array_unset_empty;

class Expression extends AbstractExpression
{
    protected array $groups = [];
    protected bool $allowTruncationOperator = false;
    protected bool $allowRelevanceOperators = false;

    public function getAllowTruncationOperator(): bool
    {
        return $this->allowTruncationOperator;
    }
    public function setAllowTruncationOperator(bool $value): static
    {
        $this->allowTruncationOperator = $value;
        return $this;
    }

    public function getAllowRelevanceOperators(): bool
    {
        return $this->allowRelevanceOperators;
    }
    public function setAllowRelevanceOperators(bool $value): static
    {
        $this->allowRelevanceOperators = $value;
        return $this;
    }

    public function include(string ...$words): static
    {
        $words = $this->cleanWords($words, true);

        if (count($words) > 1) {
            $this->groups[] = '+(' . implode(' ', $words) .')';
        } else {
            $this->groups[] = '+' . $words[0];
        }

        return $this;
    }

    public function exclude(string ...$words): static
    {
        $words = $this->cleanWords($words, true);

        if (count($words) > 1) {
            $this->groups[] = '-(' . implode(' ', $words) .')';
        } else {
            $this->groups[] = '-' . $words[0];
        }

        return $this;
    }

    public function optional(string ...$words): static
    {
        $words = $this->cleanWords($words, true);

        if (count($words) > 1) {
            $this->groups[] = '(' . implode(' ', $words) .')';
        } else {
            $this->groups[] = $words[0];
        }

        return $this;
    }

    public function distance(int $distance, string ...$words): static
    {
        $words = $this->cleanWords($words, false);

        $words = implode(' ', $words);

        // Only one word so just make it optional
        if (!str_contains($words, ' ')) {
            return $this->optional($words);
        }

        $this->groups[] = '"' . $words .'" @' . strval($distance);

        return $this;
    }

    public function negate(string ...$words): static
    {
        $words = $this->cleanWords($words, true);

        if (count($words) > 1) {
            $this->groups[] = '~(' . implode(' ', $words) .')';
        } else {
            $this->groups[] = '~' . $words[0];
        }

        return $this;
    }

    private function cleanWords(array $words, bool $allowPhrases): array
    {
        $words = array_map(
            function($value) use($allowPhrases) {
                $value = trim($value);

                $startOperator = '';
                $endOperator = '';

                if ($this->getAllowTruncationOperator()) {
                    if (str_starts_with($value, '*')) {
                        $startOperator = '*';
                    }

                    if (str_ends_with($value, '*')) {
                        $endOperator = '*';
                    }
                }

                if ($this->getAllowRelevanceOperators()) {
                    if (str_starts_with($value, '>')) {
                        $startOperator = '>';
                    }

                    if (str_starts_with($value, '<')) {
                        $startOperator = '<';
                    }
                }

                $value = preg_replace('/[+-<>~()*@"]/', '', $value) ?? '';

                if ($value === '') {
                    return $value;
                }

                $value = $startOperator . $value . $endOperator;

                if ($allowPhrases && str_contains($value, ' ')) {
                    $value = '"' . $value . '"';
                }

                return $value;
            },
            $words
        );

        return pyncer_array_unset_empty($words);
    }

    public function getQueryString(): string
    {
        return implode(' AND ', $this->groups);
    }
}
