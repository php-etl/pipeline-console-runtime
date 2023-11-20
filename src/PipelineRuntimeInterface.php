<?php

declare(strict_types=1);

namespace Kiboko\Component\Runtime\Pipeline;

use Kiboko\Contract\Pipeline\ExtractingInterface;
use Kiboko\Contract\Pipeline\LoadingInterface;
use Kiboko\Contract\Pipeline\TransformingInterface;
use Kiboko\Contract\Satellite\RunnableInterface;

interface PipelineRuntimeInterface extends ExtractingInterface, TransformingInterface, LoadingInterface, RunnableInterface
{
}
