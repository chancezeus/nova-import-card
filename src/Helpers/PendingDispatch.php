<?php

namespace Sparclex\NovaImportCard\Helpers;

use Illuminate\Foundation\Bus\PendingDispatch as BasePendingDispatch;

class PendingDispatch extends BasePendingDispatch
{
    /**
     * @return int|null
     * @throws \Exception
     */
    public function getJobStatusId()
    {
        if ($this->job instanceof QueueImport) {
            return $this->job->getJobStatusId();
        }

        return null;
    }
}
