<?php
namespace Pyncer\Database\Sql\Expression;

use Pyncer\Database\Expression\AbstractExpression;

use function Pyncer\Array\unset_empty as pyncer_array_unset_empty;

class Expression extends AbstractExpression
{
    protected array $groups = [];

    public function includes(string ...$words): static
    {
        $words = $this->cleanWords($words);

        if (count($words) > 1) {
            $groups[] = '+(' . implode(' ', $words) .')';
        } else {
            $groups[] = '+' . $words[0];
        }

        return $this;
    }

    public function excludes(string ...$words): static
    {
        $words = $this->cleanWords($words);

        if (count($words) > 1) {
            $groups[] = '-(' . implode(' ', $words) .')';
        } else {
            $groups[] = '-' . $words[0];
        }

        return $this;
    }

    public function optional(string ...$words): static
    {
        $words = $this->cleanWords($words);

        if (count($words) > 1) {
            $groups[] = '(' . implode(' ', $words) .')';
        } else {
            $groups[] = $words[0];
        }

        return $this;
    }

    public function distance(int $distance, string ...$words): static
    {
        $words = implode(' ', $words);

        // Only one word so just make it optional
        if (!str_contains($words, ' ')) {
            return $this->optional($words);
        }

        $groups[] = '"' . $words .'" @' . strval($distance);

        return $this;
    }

    public function negates(string ...$words): static
    {
        $words = $this->cleanWords($words);

        if (count($words) > 1) {
            $groups[] = '~(' . implode(' ', $words) .')';
        } else {
            $groups[] = '~' . $words[0];
        }

        return $this;
    }

    private function cleanWords(array $words, bool $allowPhrases = false): array
    {
        $words = array_map(
            function($value) use($allowPhrases) {
                $value = trim($value);

                $value = preg_replace('/[+\-<>~()*@"]/', '', $value) ?? '';

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
