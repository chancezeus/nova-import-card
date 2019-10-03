<?php

namespace Sparclex\NovaImportCard\Helpers;

use Imtigger\LaravelJobStatus\Trackable;
use Maatwebsite\Excel\Jobs\QueueImport as BaseQueueImport;

class QueueImport extends BaseQueueImport
{
    use Trackable;

    public function __construct()
    {
        $this->prepareStatus();

        $this->progressMax = 1;
    }

    /**
     * @param array $chain
     *
     * @return $this
     */
    public function chain($chain)
    {
        foreach ($chain as $idx => $job) {
            if (!($job instanceof ReadChunk)) {
                continue;
            }

            $this->progressMax = $this->progressMax + 1;
            $job->setStatusId($this->statusId);
            $job->setCurrentJobNumber($this->progressMax);
        }

        parent::chain($chain);

        return $this;
    }

    /**
     * Dispatch the job with the given arguments.
     *
     * @return \Sparclex\NovaImportCard\Helpers\PendingDispatch
     */
    public static function dispatch()
    {
        return new PendingDispatch(new static());
    }

    /**
     * Dispatch the next job on the chain.
     *
     * @return void
     */
    public function dispatchNextJobInChain()
    {
        $this->incrementProgress();

        parent::dispatchNextJobInChain();
    }

    public function handle()
    {
        $this->setProgressMax($this->progressMax);
    }
}
