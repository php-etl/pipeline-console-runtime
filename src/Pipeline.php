<?php

declare(strict_types=1);

namespace Kiboko\Component\Runtime\Pipeline;

use Kiboko\Component\Runtime\Pipeline\Step\Step;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

final class Pipeline
{
    /** @var list<Step> */
    private array $steps = [];
    private readonly ConsoleSectionOutput $section;

    public function __construct(
        private readonly ConsoleOutput $output,
        string $index,
        string $label,
    ) {
        $this->section = $output->section();
        $this->section->writeln('');
        $this->section->writeln(sprintf('<fg=green> % 2s. %-50s</>', $index, $label));
    }

    public function withStep(string $label): Step
    {
        return $this->steps[] = new Step($this->output, \count($this->steps) + 1, $label);
    }

    public function update(): void
    {
        foreach ($this->steps as $step) {
            $step->update();
        }
    }
}
