<?php

namespace App\Action;

final readonly class ActionOption
{
    /**
     * @param array<string, string> $choices
     */
    private function __construct(
        public OptionType $type,
        public int $min = 0,
        public int $max = 0,
        public int $step = 1,
        public int $default = 0,
        public array $choices = [],
    ) {
    }

    public static function range(int $min, int $max, int $step, int $default): self
    {
        return new self(OptionType::Range, $min, $max, $step, $default);
    }

    /**
     * @param array<string, string> $choices
     */
    public static function choice(array $choices): self
    {
        return new self(OptionType::Choice, choices: $choices);
    }

    public function isRoutable(string $raw): bool
    {
        return $this->type === OptionType::Choice || ctype_digit($raw);
    }

    public function isValid(string $raw): bool
    {
        if ($this->type === OptionType::Choice) {
            return array_key_exists($raw, $this->choices);
        }

        return (int) $raw >= $this->min && (int) $raw <= $this->max;
    }

    public function normalize(string $raw): int|string
    {
        return $this->type === OptionType::Range ? (int) $raw : $raw;
    }
}
