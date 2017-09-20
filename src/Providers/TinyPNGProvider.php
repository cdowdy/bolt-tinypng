<?php


namespace Bolt\Extension\cdowdy\tinypng\Providers;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Tinify;
use Bolt\Extension\cdowdy\tinypng\Handler\TinyPNGUpload;
use Bolt\Extension\cdowdy\tinypng\Handler\TinyPNGOptimize;

class TinyPNGProvider implements ServiceProviderInterface
{
    /**
     * @var
     */
    private $config;


    /**
     * TinyPNGProvider constructor.
     * @param $config
     */
    public function __construct( $config )
    {
        $this->config = $config;
    }


    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app['tinypng.optimize'] = $app->share(
            function( $app ) {
                return new TinyPNGOptimize( $app, $this->config );
            }
        );

        $app['tinypng.upload'] = $app->share(
            function( $app ) {
                return new TinyPNGUpload( $app, $this->config );
            }
        );
    }


    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
        // TODO: Implement boot() method.
    }
}