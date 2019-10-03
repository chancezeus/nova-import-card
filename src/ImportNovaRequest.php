<?php

namespace Sparclex\NovaImportCard;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class ImportNovaRequest extends NovaRequest
{
    /** @var array */
    protected $data;

    /** @var \Laravel\Nova\Resource */
    protected $resource;

    /**
     * @param \Laravel\Nova\Resource $resource
     * @param array $data
     */
    public function __construct(Resource $resource, array $data = [])
    {
        parent::__construct();

        $this->resource = $resource;
        $this->data = $data;
    }

    /**
     * Retrieve an input item from the request.
     *
     * @param string|null $key
     * @param string|array|null $default
     * @return string|array|null
     */
    public function input($key = null, $default = null)
    {
        return data_get($this->data, $key, $default);
    }

    /**
     * Get the class name of the resource being requested.
     *
     * @return mixed
     */
    public function resource()
    {
        return $this->resource;
    }
}
