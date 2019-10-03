<?php

namespace Sparclex\NovaImportCard\Contracts;

use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

interface Importer extends ToModel, WithValidation, WithHeadingRow
{
    /**
     * @param string $resourceClass
     */
    public function __construct(string $resourceClass);

    /**
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile|null $filePath
     * @param string|null $disk
     * @param string|null $readerType
     *
     * @return \Maatwebsite\Excel\Importer|PendingDispatch
     * @throws \Maatwebsite\Excel\Exceptions\NoFilePathGivenException
     */
    public function import($filePath = null, string $disk = null, string $readerType = null);
}
