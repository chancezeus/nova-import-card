<?php

namespace Sparclex\NovaImportCard\Helpers;

use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\Importable as BaseImportable;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Files\Filesystem;
use Maatwebsite\Excel\Importer;
use Maatwebsite\Excel\QueuedWriter;
use Maatwebsite\Excel\Writer;

trait Importable
{
    use BaseImportable;

    /**
     * @return Importer
     */
    private function getImporter(): Importer
    {
        return new Excel(
            App::make(Writer::class),
            App::make(QueuedWriter::class),
            App::make(Reader::class),
            App::make(Filesystem::class)
        );
    }
}
