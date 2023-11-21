<?php

declare(strict_types=1);

namespace Kiboko\Component\Runtime\Pipeline\Step;

use Kiboko\Contract\Pipeline\StepStateInterface;

final class MemoryState implements StepStateInterface
{
    private array $metrics = [
        'accept' => 0,
        'reject' => 0,
        'error' => 0,
    ];

    public function __construct(
        private readonly ?StepStateInterface $decorated = null,
    ) {
    }

    public function accept(int $count = 1): void
    {
        $this->metrics['accept'] += $count;
        $this->decorated?->accept($count);
    }

    public function reject(int $count = 1): void
    {
        $this->metrics['reject'] += $count;
        $this->decorated?->reject($count);
    }

    public function error(int $count = 1): void
    {
        $this->metrics['error'] += $count;
        $this->decorated?->error($count);
    }

    public function observeAccept(): callable
    {
        return fn () => $this->metrics['accept'];
    }

    public function observeError(): callable
    {
        return fn () => $this->metrics['accept'];
    }

    public function observeReject(): callable
    {
        return fn () => $this->metrics['reject'];
    }
}
