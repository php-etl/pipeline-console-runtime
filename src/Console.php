<?php declare(strict_types=1);

namespace Kiboko\Component\Runtime\Pipeline;

use Kiboko\Component\State;
use Kiboko\Contract\Pipeline\ExtractorInterface;
use Kiboko\Contract\Pipeline\TransformerInterface;
use Kiboko\Contract\Pipeline\LoaderInterface;
use Kiboko\Contract\Pipeline\PipelineInterface;
use Kiboko\Contract\Pipeline\RejectionInterface;
use Kiboko\Contract\Pipeline\StateInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

final class Console implements PipelineRuntimeInterface
{
    private State\StateOutput\Pipeline $state;

    public function __construct(
        ConsoleOutput $output,
        private PipelineInterface $pipeline,
        ?State\StateOutput\Pipeline $state = null
    ) {
        $this->state = $state ?? new State\StateOutput\Pipeline($output, 'A', 'Pipeline');
    }

    public function extract(
        ExtractorInterface $extractor,
        RejectionInterface $rejection,
        StateInterface $state,
    ): self {
        $this->pipeline->extract($extractor, $rejection, $state = new State\MemoryState($state));

        $this->state->withStep('extractor')
            ->addMetric('read', $state->observeAccept())
            ->addMetric('error', fn () => 0)
            ->addMetric('rejected', $state->observeReject());

        return $this;
    }

    public function transform(
        TransformerInterface $transformer,
        RejectionInterface $rejection,
        StateInterface $state,
    ): self {
        $this->pipeline->transform($transformer, $rejection, $state = new State\MemoryState($state));

        $this->state->withStep('transformer')
            ->addMetric('read', $state->observeAccept())
            ->addMetric('error', fn () => 0)
            ->addMetric('rejected', $state->observeReject());

        return $this;
    }

    public function load(
        LoaderInterface $loader,
        RejectionInterface $rejection,
        StateInterface $state,
    ): self {
        $this->pipeline->load($loader, $rejection, $state = new State\MemoryState($state));

        $this->state->withStep('loader')
            ->addMetric('read', $state->observeAccept())
            ->addMetric('error', fn () => 0)
            ->addMetric('rejected', $state->observeReject());

        return $this;
    }

    public function run(int $interval = 1000): int
    {
        $line = 0;
        foreach ($this->pipeline->walk() as $item) {
            if ($line++ % $interval === 0) {
                $this->state->update();
            }
        };
        $this->state->update();

        return $line;
    }
}
