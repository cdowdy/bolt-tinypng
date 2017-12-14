<?php


namespace Bolt\Extension\cdowdy\tinypng\Handler;

use Tinify;
use Bolt\Extension\cdowdy\betterthumbs\Helpers\ConfigHelper;

use Silex;


class TinyPNGOptimize
{

    protected $extConfig;
    /**
     * TinyPNGOptimize constructor.
     * @param Silex\Application $app
     * @param                   $extensionConfig
     */
    public function __construct( Silex\Application $app, $extensionConfig )
    {
        $this->app       = $app;
        $this->extConfig = $extensionConfig;
    }


    /**
     * @return bool
     */
    public function tinypngValidate(  )
    {

        try {
            Tinify\setKey( $this->extConfig['tinypng_apikey'] );
            Tinify\validate();
            // Use the Tinify API client.
        } catch ( Tinify\AccountException $e ) {

            $message = "TinyPNG Account Exception: " . $e->getMessage();
            $this->app['logger.system']->error( $message, [ 'event' => 'authentication' ] );

            $flash = "There was a problem with your API key or with your API account. Your request could not be authorized. If your compression limit is reached, you can wait until the next calendar month or upgrade your subscription. After verifying your API key and your account status, you can retry the request.";
            $this->app['logger.flash']->error( 'TinyPNG:: ' . $flash . ' <br/> ' . $e->getMessage() );
            // Verify your API key and account limit.
        } catch ( \Exception $e ) {
            $this->app['logger.system']->error( $e->getMessage(), [ 'event' => 'exception' ] );
        }

        return true;
    }


    /**
     * @param             $image
     * @param             $newName
     * @param             $dataToPreserve
     *
     * @return array
     */
    public function tryOptimization( $image, $newName, $dataToPreserve )
    {
        $optimized = [];
        $imagename = $this->doRename( $image, $newName );

        try {
            $source = \Tinify\fromFile( $image );

            if ( $dataToPreserve !== 'none' ) {

                if ( $dataToPreserve === 'location' ) {
                    $preserved = $source->preserve( "location" );

                } elseif ( $dataToPreserve === 'creation' ) {
                    $preserved = $source->preserve( "creation" );

                } elseif ( $dataToPreserve === 'copyright' ) {
                    $preserved = $source->preserve( "copyright" );
                } else {
                    $preserved = $source->preserve( "location", "creation", "copyright" );
                }

                $optimized[] = $preserved->toFile( $imagename );
            } else {
                $optimized[] = $source->toFile( $imagename );
            }

            // Use the Tinify API client.
        } catch ( Tinify\ClientException $e ) {

            $message = "TinyPNG Client Exception: " . $e->getMessage();

            $this->app['logger.system']->error( $message, [ 'event' => 'exception' ] );

            $this->app['logger.flash']->error( 'The request could not be completed because of a problem with the submitted data: ' . $e->getMessage() );

            // Check your source image and request options.
        } catch ( Tinify\ServerException $e ) {
            $message = "TinyPNG Server Exception: " . $e->getMessage();
            $this->app['logger.system']->error( $message, [ 'event' => 'exception' ] );

            $flash = "The request could not be completed because of a temporary problem with the Tinify API. It is safe to retry the request after a few minutes. If you see this error repeatedly for a longer period of time, please contact support@tinify.com";

            $this->app['logger.flash']->error( 'TinyPNG Server Exception: ' . $flash );

            // Temporary issue with the Tinify API.
        } catch ( Tinify\ConnectionException $e ) {

            $message = "TinyPNG Connection Exception: " . $e->getMessage();
            $this->app['logger.system']->error( $message, [ 'event' => 'exception' ] );

            $flash = "The request could not be sent because there was an issue connecting to the Tinify API. You should verify your network connection. It is safe to retry the request";
            $this->app['logger.flash']->error( 'TinyPNG Connection Exception: ' . $flash );
            // A network connection error occurred.
        } catch ( \Exception $e ) {
            $this->app['logger.system']->error( $e->getMessage(), [ 'event' => 'exception' ] );
        }

        return $optimized;
    }

    /**
     * @param $image
     * @param $newName
     *
     * @return string
     */
    public function doRename( $image, $newName )
    {
        $getExt = pathinfo( $image );


        if ( empty( $newName ) ) {
            return $image;
        } else {
            return $newName . '.' . $getExt['extension'];
        }
    }

    /**
     * @return null|string
     */
    public function getCompressionCount()
    {

        $validateKey = $this->app['tinypng.optimize']->tinypngValidate();

        if ( $validateKey ) {
            $comressionsThisMonth = Tinify\getCompressionCount();
        } else {
            $comressionsThisMonth = 'your api key isn\'t valid';

        }

        return $comressionsThisMonth;
    }
}