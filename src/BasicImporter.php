<?php

namespace Sparclex\NovaImportCard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\Nova\Resource;
use Sparclex\NovaImportCard\Contracts\Importer;
use Sparclex\NovaImportCard\Helpers\Importable;

class BasicImporter implements Importer
{
    use Importable;
    use InteractsWithQueue;
    use SerializesModels;

    /** @var \Laravel\Nova\Resource|string */
    protected $resourceClass;

    /**
     * BasicImporter constructor.
     * @param string $resourceClass
     */
    public function __construct(string $resourceClass)
    {
        $this->resourceClass = $resourceClass;
    }

    /**
     * @param array $row
     *
     * @return Model|Model[]|null
     */
    public function model(array $row)
    {
        /** @var \Laravel\Nova\Resource $resourceClass */
        $resourceClass = $this->resourceClass;

        [$model] = $resourceClass::fill(new ImportNovaRequest($this->getResource(), $row), $this->getModel());

        return $model;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        /** @var \Laravel\Nova\Resource $resourceClass */
        $resourceClass = $this->resourceClass;

        return $resourceClass::rulesForCreation(new ImportNovaRequest($this->getResource()));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getModel(): Model
    {
        /** @var \Laravel\Nova\Resource $resourceClass */
        $resourceClass = $this->resourceClass;

        return $resourceClass::newModel();
    }

    /**
     * @return \Laravel\Nova\Resource
     */
    protected function getResource(): Resource
    {
        /** @var \Laravel\Nova\Resource $resourceClass */
        $resourceClass = $this->resourceClass;

        /** @var \Laravel\Nova\Resource $instance */
        $instance = new $resourceClass($this->getModel());

        return $instance;
    }
}
