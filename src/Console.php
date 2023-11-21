<?php

declare(strict_types=1);

namespace Kiboko\Component\Runtime\Pipeline;

use Kiboko\Contract\Pipeline\ExtractorInterface;
use Kiboko\Contract\Pipeline\LoaderInterface;
use Kiboko\Contract\Pipeline\PipelineInterface;
use Kiboko\Contract\Pipeline\StepCodeInterface;
use Kiboko\Contract\Pipeline\StepRejectionInterface;
use Kiboko\Contract\Pipeline\StepStateInterface;
use Kiboko\Contract\Pipeline\TransformerInterface;
use Kiboko\Contract\Pipeline\WalkableInterface;
use Kiboko\Component\Runtime\Pipeline\Step\MemoryState;
use Symfony\Component\Console\Output\ConsoleOutput;

final class Console implements PipelineRuntimeInterface
{
    private readonly Pipeline $state;

    public function __construct(
        ConsoleOutput $output,
        private readonly PipelineInterface&WalkableInterface $pipeline,
        ?Pipeline $state = null
    ) {
        $this->state = $state ?? new Pipeline($output, 'A', 'Pipeline');
    }

    public function extract(
        StepCodeInterface $step,
        ExtractorInterface $extractor,
        StepRejectionInterface $rejection,
        StepStateInterface $state,
    ): self {
        $this->pipeline->extract($step, $extractor, $rejection, $state = new MemoryState($state));

        $this->state->withStep((string) $step)
            ->addMetric('read', $state->observeAccept())
            ->addMetric('error', $state->observeError())
            ->addMetric('rejected', $state->observeReject())
        ;

        return $this;
    }

    public function transform(
        StepCodeInterface $step,
        TransformerInterface $transformer,
        StepRejectionInterface $rejection,
        StepStateInterface $state,
    ): self {
        $this->pipeline->transform($step, $transformer, $rejection, $state = new MemoryState($state));

        $this->state->withStep((string) $step)
            ->addMetric('read', $state->observeAccept())
            ->addMetric('error', fn () => 0)
            ->addMetric('rejected', $state->observeReject())
        ;

        return $this;
    }

    public function load(
        StepCodeInterface $step,
        LoaderInterface $loader,
        StepRejectionInterface $rejection,
        StepStateInterface $state,
    ): self {
        $this->pipeline->load($step, $loader, $rejection, $state = new MemoryState($state));

        $this->state->withStep((string) $step)
            ->addMetric('read', $state->observeAccept())
            ->addMetric('error', fn () => 0)
            ->addMetric('rejected', $state->observeReject())
        ;

        return $this;
    }

    public function run(int $interval = 1000): int
    {
        $line = 0;
        foreach ($this->pipeline->walk() as $item) {
            if (0 === $line++ % $interval) {
                $this->state->update();
            }
        }
        $this->state->update();

        return $line;
    }
}
