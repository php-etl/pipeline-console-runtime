<?php

declare(strict_types=1);

namespace Kiboko\Component\Runtime\Pipeline\Step;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

final class Step
{
    /** @var array<string, callable> */
    private array $metrics = [];
    private readonly ConsoleSectionOutput $section;

    public function __construct(
        private readonly ConsoleOutput $output,
        int $index,
        string $label,
    ) {
        $this->section = $this->output->section();
        $this->section->writeln(sprintf('<fg=cyan> % 2d. %-50s</>', $index, $label));
        $this->section->writeln('');
    }

    public function addMetric(string $label, callable $callback): self
    {
        $this->metrics[$label] = $callback;

        return $this;
    }

    public function update(): void
    {
        $this->section->clear(1);
        $this->section
            ->writeln('     '.implode(', ', array_map(
                fn (string $label, callable $callback) => sprintf('%s <fg=cyan>%d</>', $label, ($callback)()),
                array_keys($this->metrics),
                array_values($this->metrics),
            )))
        ;
    }
}
