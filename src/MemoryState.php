<?php

declare(strict_types=1);

namespace Kiboko\Component\Runtime\Pipeline;

use Kiboko\Contract\Pipeline\StateInterface;
use Kiboko\Contract\Pipeline\StepCodeInterface;

final class MemoryState implements StateInterface
{
    private array $metrics = [];

    public function __construct(
        private readonly ?StateInterface $decorated = null,
    ) {
    }

    public function initialize(int $start = 0): void
    {
        $this->metrics = [
            'accept' => 0,
            'reject' => 0,
            'error' => 0,
        ];

        $this->decorated?->initialize($start);
    }

    public function accept(StepCodeInterface $step, int $count = 1): void
    {
        $this->metrics['accept'] += $count;
        $this->decorated?->accept($step);
    }

    public function reject(StepCodeInterface $step, int $count = 1): void
    {
        $this->metrics['reject'] += $count;
        $this->decorated?->reject($step);
    }

    public function error(StepCodeInterface $step, int $count = 1): void
    {
        $this->metrics['error'] += $count;
        $this->decorated?->error($step);
    }

    public function observeAccept(): callable
    {
        return fn () => $this->metrics['accept'];
    }

    public function observeError(): callable
    {
        return fn () => $this->metrics['error'];
    }

    public function observeReject(): callable
    {
        return fn () => $this->metrics['reject'];
    }

    public function teardown(): void
    {
        $this->decorated?->teardown();
    }
}
