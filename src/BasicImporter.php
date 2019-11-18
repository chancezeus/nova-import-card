<?php

namespace Sparclex\NovaImportCard;

use Illuminate\Support\Collection;
use Laravel\Nova\Resource;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class BasicImporter implements ToModel, WithValidation, WithHeadingRow
{
    use Importable;

    /** @var \Laravel\Nova\Resource */
    protected $resource;

    /** @var \Illuminate\Support\Collection */
    protected $attributes;

    /** @var array */
    protected $rules;

    /** @var string */
    protected $modelClass;

    /**
     * @param \Laravel\Nova\Resource $resource
     * @param \Illuminate\Support\Collection $attributes
     * @param array $rules
     * @param string $modelClass
     */
    public function __construct(Resource $resource, Collection $attributes, array $rules, string $modelClass)
    {
        $this->resource = $resource;
        $this->attributes = $attributes;
        $this->rules = $rules;
        $this->modelClass = $modelClass;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Model[]|null
     */
    public function model(array $row)
    {
        [$model] = $this->resource::fill(new ImportNovaRequest($row), $this->resource::newModel());

        return $model;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return $this->rules;
    }
}
