<?php


namespace Bolt\Extension\cdowdy\tinypng\Handler;


use Tinify;
use Bolt\Extension\cdowdy\betterthumbs\Helpers\ConfigHelper;
use Silex;


class TinyPNGUpload {


	/**
	 * TinyPNGUpload constructor.
	 *
	 * @param Silex\Application $app
	 * @param                   $extensionConfig
	 */
	public function __construct( Silex\Application $app, $extensionConfig )
	{
		$this->app       = $app;
		$this->extConfig = $extensionConfig;
	}


	/**
	 * @param $method
	 *
	 * @return string
	 */
	public function tinyPNGMethod( $method )
	{
		$validMethods    = [ 'scale', 'fit', 'cover' ];
		$normalizeMethod = strtolower( $method );

		if ( empty( $normalizeMethod ) || ! in_array( $normalizeMethod,
				$validMethods ) || ! isset( $normalizeMethod )
		) {
			$resizeMethod = 'scale';
		} else {
			$resizeMethod = $normalizeMethod;
		}

		return $resizeMethod;

	}

	public function tinyPNGDoResize( $image, $resizeMethod )
	{
//		$configHelper = new ConfigHelper( $this->extConfig );
		$method = $this->tinyPNGMethod( $resizeMethod );
//		$metaData = $this->tinyPNGMetaData($dataPreserve);

		$resizedImages = [];


		try {

			$sourceImage = Tinify\fromFile( $image );

			switch ( $method ) {
				case 'scale':
					$resized = $sourceImage->resize( $this->tinyPNGResizeScale() );
					break;
				case 'fit':
				case 'cover':
					$resized = $sourceImage->resize( $this->tinyPNGResizeFitCover( $method ) );
					break;
				default:
					$resized = $sourceImage->resize( $this->tinyPNGResizeScale() );
			}

			$resizedImages[] = $resized->toFile( $image );

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


		return $resizedImages;

	}

	/**
	 * @param $param
	 *
	 * @return string
	 * check for width or heights for the scale method
	 */
	public function checkForWidthHeights( $param )
	{
		$config = $this->extConfig['tinypng_upload'];

		if ( isset( $config[ $param ] ) || array_key_exists( $param, $config ) ) {
			$option = $config[ $param ];
		} else {
			$option = '';
		}

		return $option;
	}


	/**
	 *
	 * @return array
	 * here we'll see what the resize method is and then check to make sure that a width or height is there
	 * and if we need it
	 */
	protected function tinyPNGResizeScale()
	{
		$width  = $this->checkForWidthHeights( 'width' );
		$height = $this->checkForWidthHeights( 'height' );

		$params = [];

		$noHeightOrWidths = 'To Use The Scale Method You MUST set a Width or Height in the Extensions Config';

		if ( empty( $width ) && empty( $height ) ) {
			// if both are empty we will set a flash message and send info to the logger
			$this->app['logger.flash']->error( 'TinyPNG:: ' . $noHeightOrWidths );

			$this->app['logger.system']->error( 'TinyPNG:: ' . $noHeightOrWidths, [ 'event' => 'extension' ] );

		} else {
			$params = [
				'method' => 'scale',
				'width'  => $width
			];
		}

		if ( ! empty( $width ) && empty( $height ) ) {
			// if width isn't empty and height is we'll set the width and discard the height
			$params = [
				'method' => 'scale',
				'width'  => $width
			];
		}

		if ( empty( $width ) && ! empty( $height ) ) {

			$params = [
				'method' => 'scale',
				'height' => $height
			];
		}

		return $params;
	}


	protected function tinyPNGResizeFitCover( $method )
	{
		$width        = $this->checkForWidthHeights( 'width' );
		$height       = $this->checkForWidthHeights( 'height' );
		$resizeMethod = $this->tinyPNGMethod( $method );

		$params = [];

		$noHeightOrWidths = "To Use The {$resizeMethod} Method You MUST set a Width and Height in the Extensions Config";

		if ( empty( $width ) && empty( $height ) ) {
			// if both are empty we will set a flash message and send info to the logger
			$this->app['logger.flash']->error( 'TinyPNG:: ' . $noHeightOrWidths );

			$this->app['logger.system']
				->error( 'TinyPNG:: ' . $noHeightOrWidths, [ 'event' => 'extension' ] );

		} else {
			$params = [
				'method' => $resizeMethod,
				'width'  => $width,
				'height' => $height
			];
		}

		if ( ! empty( $width ) && empty( $height ) ) {
			$this->app['logger.flash']->error( 'TinyPNG:: ' . "You Must Set A Height To Use the {$resizeMethod} Method" );

			$this->app['logger.system']
				->error( 'TinyPNG:: ' . "You Must Set A Height To Use the {$resizeMethod} Method",
					[ 'event' => 'extension' ] );
		}

		if ( empty( $width ) && ! empty( $height ) ) {
			$this->app['logger.flash']->error( 'TinyPNG:: ' . "You Must Set A Width To Use the {$resizeMethod} Method" );

			$this->app['logger.system']
				->error( 'TinyPNG:: ' . "You Must Set A Width To Use the {$resizeMethod} Method",
					[ 'event' => 'extension' ] );
		}

		return $params;
	}


	/**
	 * @param array $metadata
	 *
	 * @return string
	 */
	protected function tinyPNGMetaData( array $metadata )
	{
		$extConfigUpload = $this->extConfig['tinypng_upload'];
		$validMetaData   = [ 'location', 'creation', 'copyright' ];

		$metaToArray   = $this->optionToArray( $metadata );
		$normalizeData = array_map( 'strtolower', $metaToArray );

		$savedData = '';

		// check to see if metadata portion is set / uncommented
		if ( array_key_exists( 'metadata', $extConfigUpload )
		     && ! empty( $extConfigUpload['metadata'] )
		) {

			// if its set but the options are wrong show a flash message
			if ( ! in_array( $normalizeData, $validMetaData ) ) {

				$this->app['session']
					->getFlashBag()
					->set( 'error',
						'TinyPNG:: ' . "Valid Metadata to save is <code>location</code>, <code>creation</code> and or <code>copyright</code> " );

				// all things are good and the options are correct give us the data to save string
			} else {

				$savedData = '"' . implode( '", "', $normalizeData ) . '"';
			}

			// make data none if none of the above pass
		} else {
			$savedData = 'none';
		}

		return $savedData;
	}


	/**
	 * @param $option
	 *
	 * @return array
	 * make sure the option we are passing is an array
	 */
	protected function optionToArray( $option )
	{
		// check if the option that we need to be an array is in fact in an array
		$isArray = is_array( $option ) ? $option : array( $option );

		// return the array and make sure it is not empty
		return array_filter( $isArray );

	}

}