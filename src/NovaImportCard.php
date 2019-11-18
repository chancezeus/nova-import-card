<?php

namespace Sparclex\NovaImportCard;

use Laravel\Nova\Card;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Resource;

class NovaImportCard extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = '1/2';

    /**
     * @param string $resource
     */
    public function __construct(string $resource)
    {
        parent::__construct();

        if (!is_subclass_of($resource, Resource::class)) {
            throw new \InvalidArgumentException(sprintf('Expected $resource to extend %s got %s', Resource::class, $resource));
        }

        /** @var \Laravel\Nova\Resource $resource */

        $this->withMeta([
            'fields' => [
                new File('File'),
            ],
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
        return 'nova-import-card';
    }
}
