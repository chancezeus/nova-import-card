<?php

namespace Sparclex\NovaImportCard\Helpers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\ChunkReader as BaseChunkReader;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Imports\HeadingRowExtractor;
use Maatwebsite\Excel\Jobs\AfterImportJob;
use Maatwebsite\Excel\Reader;

class ChunkReader extends BaseChunkReader
{
    /**
     * @param WithChunkReading $import
     * @param Reader $reader
     * @param TemporaryFile $temporaryFile
     *
     * @return \Illuminate\Foundation\Bus\PendingDispatch|\Sparclex\NovaImportCard\Helpers\PendingDispatch|null
     * @throws \Exception
     */
    public function read(WithChunkReading $import, Reader $reader, TemporaryFile $temporaryFile)
    {
        if (!($import instanceof ShouldQueue)) {
            return parent::read($import, $reader, $temporaryFile);
        }

        if ($import instanceof WithEvents && isset($import->registerEvents()[BeforeImport::class])) {
            $reader->beforeImport($import);
        }

        $chunkSize = $import->chunkSize();
        $totalRows = $reader->getTotalRows();
        $worksheets = $reader->getWorksheets($import);

        if ($import instanceof WithProgressBar) {
            $import->getConsoleOutput()->progressStart(array_sum($totalRows));
        }

        $jobs = new Collection();
        foreach ($worksheets as $name => $sheetImport) {
            $startRow = HeadingRowExtractor::determineStartRow($sheetImport);
            $totalRows[$name] = $sheetImport instanceof WithLimit ? $sheetImport->limit() : $totalRows[$name];

            for ($currentRow = $startRow; $currentRow <= $totalRows[$name]; $currentRow += $chunkSize) {
                $jobs->push(new ReadChunk(
                    $import,
                    $reader->getPhpSpreadsheetReader(),
                    $temporaryFile,
                    $name,
                    $sheetImport,
                    $currentRow,
                    $chunkSize
                ));
            }
        }

        $jobs->push(new AfterImportJob($import, $reader));

        return QueueImport::dispatch()->chain($jobs->toArray());
    }
}
