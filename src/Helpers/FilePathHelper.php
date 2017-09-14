<?php

namespace Bolt\Extension\cdowdy\tinypng\Helpers;


use Silex\Application;
use Bolt\Version as Version;

class FilePathHelper
{

    /**
     * Bolt changed how the paths are located. Prior to bolt 3.3 we could use the resources service.
     * Moving Forward its a path resolver
     * This is to make sure we have backwards compat since I dont' know if bolt did it and they have a habit of
     * removing things without backwards compat
     */


    /**
     * FilePathHelper constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }


    /**
     * Get Bolt's Files path
     * @return mixed
     *
     * Use PHP's version_compare instead of bolts Version::compare since it's "broken"
     */
    public function boltFilesPath()
    {
        if ( version_compare( Version::VERSION , '3.3.0', '>=' ) ) {
            return $this->app['path_resolver']->resolve('files');
        } else {
            return $this->app['resources']->getPath( 'filespath' );
        }
    }

    /**
     * Get Bolt's extensions path ...
     * @return mixed
     */
    public function boltExtensionsPath()
    {
        if ( version_compare( Version::VERSION , '3.3.0', '>=' ) ) {
            return $this->app['path_resolver']->resolve('extensions');
        } else {
            return $this->app['resources']->getPath('extensions');

        }
    }

}