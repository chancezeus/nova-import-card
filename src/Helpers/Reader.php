<?php

namespace Sparclex\NovaImportCard\Helpers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Factories\ReaderFactory;
use Maatwebsite\Excel\Reader as BaseReader;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

class Reader extends BaseReader
{
    /**
     * @param object $import
     * @param string|UploadedFile $filePath
     * @param string|null $readerType
     * @param string|null $disk
     *
     * @return \Illuminate\Foundation\Bus\PendingDispatch|$this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Maatwebsite\Excel\Exceptions\NoTypeDetectedException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \Exception
     */
    public function read($import, $filePath, string $readerType = null, string $disk = null)
    {
        if (!($import instanceof ShouldQueue) || !($import instanceof WithChunkReading)) {
            return parent::read($import, $filePath, $readerType, $disk);
        }

        if ($import instanceof WithEvents) {
            $this->registerListeners($import->registerEvents());
        }

        if ($import instanceof WithCustomValueBinder) {
            Cell::setValueBinder($import);
        }

        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        $temporaryFile = $this->temporaryFileFactory->make($fileExtension);
        $this->currentFile = $temporaryFile->copyFrom($filePath, $disk);
        $this->reader = ReaderFactory::make($import, $this->currentFile, $readerType);

        return (new ChunkReader)->read($import, $this, $this->currentFile);
    }
}
