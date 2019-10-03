<?php

namespace Sparclex\NovaImportCard\Helpers;

use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Imtigger\LaravelJobStatus\JobStatus;
use Imtigger\LaravelJobStatus\Trackable;
use Maatwebsite\Excel\Jobs\ReadChunk as BaseReadChunk;
use Maatwebsite\Excel\Transactions\TransactionHandler;

class ReadChunk extends BaseReadChunk
{
    use Trackable;

    /**
     * @param TransactionHandler $transaction
     *
     * @throws \Maatwebsite\Excel\Exceptions\SheetNotFoundException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function handle(TransactionHandler $transaction)
    {
        try {
            parent::handle($transaction);
        } catch (ValidationException $e) {
            $this->update([
                'status' => JobStatus::STATUS_FAILED,
                'finished_at' => Carbon::now(),
                'output' => json_encode([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ])
            ]);
        }
    }

    /**
     * Dispatch the next job on the chain.
     *
     * @return void
     */
    public function dispatchNextJobInChain()
    {
        $this->setProgressNow($this->progressNow);

        parent::dispatchNextJobInChain();
    }

    /**
     * @param int $statusId
     */
    public function setStatusId(int $statusId)
    {
        $this->statusId = $statusId;
    }

    /**
     * @param int $value
     */
    public function setCurrentJobNumber(int $value)
    {
        $this->progressNow = $value;
    }
}
