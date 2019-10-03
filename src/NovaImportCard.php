<?php

namespace Sparclex\NovaImportCard;

use Laravel\Nova\Card;
use Laravel\Nova\Fields\File;

class NovaImportCard extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = '1/2';

    public static $name = 'nova-import-card';

    /**
     * @param \Laravel\Nova\Resource|string $resource
     */
    public function __construct($resource)
    {
        parent::__construct();

        $this->withMeta([
            'fields' => [new File('File')],
            'resourceLabel' => $resource::label(),
            'resource' => $resource::uriKey(),
        ]);
    }

    /**
     * Get the component name for the element.
     *
     * @return string
     */
    public function component()
    {
        return static::$name;
    }
}
