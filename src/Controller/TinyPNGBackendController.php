<?php

namespace Bolt\Extension\cdowdy\tinypng\Controller;


use Bolt\Extension\cdowdy\tinypng\Handler\TinyPNGUpload;
use Bolt\Filesystem\Exception\IOException;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Tinify;


/**
 * TinyPNGBackend Controller class.
 *
 * @author Cory Dowdy <cory@corydowdy.com>
 */
class TinyPNGBackendController implements ControllerProviderInterface {

	/** @var array The extension's configuration parameters */
	private $config;

	/**
	 * Initiate the controller with Bolt Application instance and extension config.
	 *
	 * @param array $config
	 */
	public function __construct( array $config )
	{
		$this->config = $config;
	}

	/**
	 * Specify which method handles which route.
	 *
	 * Base route/path is '/' which gives us the overview list of all the files in "files"
	 *
	 * The other routes of "/tinypng/optimize" overwrites the original file with an optimized one
	 *
	 * "/tinypng/optimize/rename" optimizes and saves the file under a new name
	 *
	 *
	 * @param Application $app An Application instance
	 *
	 * @return ControllerCollection A ControllerCollection instance
	 */
	public function connect( Application $app )
	{
		/** @var $ctr \Silex\ControllerCollection */
		$ctr = $app['controllers_factory'];

		// /example/url/in/controller
		$ctr->match( '/', [ $this, 'allImages' ] )
		    ->bind( 'tinypng-all-images' );

		$ctr->post( '/optimize', [ $this, 'optimizeImage' ] )
		    ->bind( 'tinypng-optimize' );

		$ctr->post( '/optimize/rename', [ $this, 'renameOptimize' ] )
		    ->bind( 'tinypng-rename' );

		$ctr->post( '/optimize/upload', [ $this, 'uploadImage' ] )
		    ->bind( 'tinypng-upload-images' );

		$ctr->post( '/optimize/delete', [ $this, 'deleteImage' ] )
		    ->bind( 'tinypng-delete-image' );

//		$ctr->post( "/optimize/newUpload", [ $this, "newUploadImage" ] )
//		    ->bind( "tinypng_new_upload" );

		$ctr->before( [ $this, 'before' ] );

		return $ctr;
	}

	/**
	 * @param Request     $request
	 * @param Application $app
	 *
	 * @return null|RedirectResponse
	 */
	public function before( Request $request, Application $app )
	{
		// make sure the logged in user can view and uplooad files
		if ( ! $app['users']->isAllowed( 'files' ) ) {

			/** @var UrlGeneratorInterface $generator */
			$generator = $app['url_generator'];

			return new RedirectResponse( $generator->generate( 'dashboard' ), Response::HTTP_SEE_OTHER );
		}

		return null;
	}

	/**
	 * @param Application $app
	 *
	 * @param Request     $request
	 *
	 * @return mixed
	 */
	public function allImages( Application $app, Request $request )
	{
		$boltFilesPath = $app['resources']->getPath( 'filespath' );
		$adapter       = new Local( $boltFilesPath );
		$filesystem    = new Filesystem( $adapter );

		// recursively get all the files under '/files/'
		// don't worry about folder structure just dump em all out :)
		$fileList = $filesystem->listContents( null, true );

		$expectedMimes = $this->checkAccpetedTypes();
		$files         = $this->renderFileList( $filesystem, $fileList, $boltFilesPath, $expectedMimes );

		// get the compression count :: might wanna change this to use ajax so it's updated after a compression
		$compressionCount = $this->getCompressionCount( $app );


//		foreach ( $fileList as $object ) {
//
//			// we only want "files" here so anything else in the files directory can be "discarded"
//			// we'll also skip over if there is a ".cache" directory like from my betterthumbs extension
//			// finally we'll make sure we are only deailing with jpg/png files
//			if ( $object['type'] == 'file'
//			     && ! preg_match_all( '/^.cache\//i', $object['dirname'] )
//			     && in_array( strtolower( $filesystem->getMimetype( $object['path'] ) ), $expectedMimes )
//			) {
//
//				$imageWidthHeight = getimagesize( $boltFilesPath . '/' . $object['path'] );
//				$width            = $imageWidthHeight[0];
//				$height           = $imageWidthHeight[1];
//
//				$files[] = [
//					'filename'    => $object['basename'],
//					'located'     => $object['dirname'],
//					'imagePath'   => $object['path'],
//					'mimeType'    => $filesystem->getMimetype( $object['path'] ),
//					'filesize'    => self::bytesToHuman( $filesystem->getSize( $object['path'] ) ),
//					'imageWidth'  => $width,
//					'imageHeight' => $height,
//				];
//			}
//		}

		// create an upload form, add some constraints making sure it's an image and its a PNG or JPG
//		$uploadForm = $app['form.factory']
//			->createNamedBuilder( "form_tinypng", FormType::class )
//			->add( 'file', FileType::class,
//				[
//					'label'       => 'Upload an Image',
//					'multiple'    => true,
//					'attr'        => [
//						'class'  => 'tinypng-inputfile',
//						'accept' => 'image/jpeg,image/png'
//					],
//					'label_attr'  => [ 'class' => 'control-label' ],
//					// mutliple file upload constraints are returned as an object. A string is expected
//					// so wrap this in "Assert\All" so they actually get validated
//					'constraints' => [
//						new Assert\All( [
//							new Assert\Image( [
//								'mimeTypes'        => [
//									'image/jpeg',
//									'image/png',
//									'image/gif'
//								],
//								'mimeTypesMessage' => 'Images Must Be Either a PNG or JPG / JPEG',
//							] ),
//						] )
//					]
//				] )
////			->add( "Upload_File", SubmitType::class,
////				[
////					'attr' => [ 'class' => 'btn btn-primary tinypng-upload' ]
////				] )
//			->getForm();

		if ( $request->isMethod( 'POST' ) ) {
			$this->uploadImage( $app, $request );
		}


		// check to see if the tinypng api key is empty
		$noKey = empty( $this->config['tinypng_apikey'] );

		$tnyPngUpload = new TinyPNGUpload( $app, $this->config );

		$configMethod = isset( $this->config['tinypng_upload']['method'] )
			? $this->config['tinypng_upload']['method']
			: '';

		$methods = $tnyPngUpload->tinyPNGMethod( $configMethod );

		$checkW = $tnyPngUpload->checkForWidthHeights( 'width' );
		$checkH = $tnyPngUpload->checkForWidthHeights( 'height' );

		// context to render in our twig template
		$context = [
			'noKey'            => $noKey,
			'tinyPNG_files'    => $files,
			'compressionCount' => $compressionCount,
//			'showUpload'       => $uploadForm->createView(),
			'uploadMethod'     => $methods,
//			'saveData' => $metadata,
			'maxWidth'         => $checkW,
			'maxHeight'        => $checkH,
		];

		return $app['twig']->render( 'tinypng.imageoptimization.html.twig', $context );
	}

	protected function renderFileList( $filesystem, $fileList, $boltFilesPath, $expectedMimes )
	{
		$files = [];

		foreach ( $fileList as $object ) {

			// we only want "files" here so anything else in the files directory can be "discarded"
			// we'll also skip over if there is a ".cache" directory like from my betterthumbs extension
			// finally we'll make sure we are only deailing with jpg/png files
			if ( $object['type'] == 'file'
			     && ! preg_match_all( '/^.cache\//i', $object['dirname'] )
			     && in_array( strtolower( $filesystem->getMimetype( $object['path'] ) ), $expectedMimes )
			) {

				$imageWidthHeight = getimagesize( $boltFilesPath . '/' . $object['path'] );
				$width            = $imageWidthHeight[0];
				$height           = $imageWidthHeight[1];

				$files[] = [
					'filename'    => $object['basename'],
					'located'     => $object['dirname'],
					'imagePath'   => $object['path'],
					'mimeType'    => $filesystem->getMimetype( $object['path'] ),
					'filesize'    => self::bytesToHuman( $filesystem->getSize( $object['path'] ) ),
					'imageWidth'  => $width,
					'imageHeight' => $height,
				];
			}
		}

		return $files;
	}

	/**
	 * @param Application $app
	 * @param Request     $request
	 *
	 * @return JsonResponse
	 */
	public function optimizeImage( Application $app, Request $request )
	{
		$config = $this->config;

		// request variables to get from the posted data
		$image           = $request->get( 'image' );
		$preserveOptions = $request->get( 'preserve' );

		// get bolts filepath - can be changed by the user
		$filesPath = $app['resources']->getpath( 'filespath' );

		$adapter    = new Local( $filesPath );
		$filesystem = new Filesystem( $adapter );

		// append filespath to the front of the image we are using
		$imagePath = $filesPath . '/' . $image;

		$tinypngkey = $config['tinypng_apikey'];


		$valid = $this->tinypngValidate( $app, $tinypngkey );

		$optimized = [];
		if ( $valid ) {
			$this->tryOptimization( $app, $imagePath, '', $preserveOptions );
			$optimized[] = [
//				'optimizedImage' =>,
//				'filelist' => $this->renderFileList($filesystem)
				'compressionCount' => $this->getCompressionCount( $app ),
				'optimizedSize'    => self::bytesToHuman( $filesystem->getSize( $image ) ),
			];
		}


		return new JsonResponse( $optimized );
	}

	/**
	 * @param Application $app
	 * @param Request     $request
	 *
	 * @return JsonResponse
	 */
	public function renameOptimize( Application $app, Request $request )
	{
		$config = $this->config;

		// request variables to get from the posted data
		$image           = $request->get( 'image' );
		$newImageName    = $request->get( 'newName' );
		$preserveOptions = $request->get( 'preserve' );


		// get bolts filepath - can be changed by the user
		$filesPath = $app['resources']->getpath( 'filespath' );

		$adapter    = new Local( $filesPath );
		$filesystem = new Filesystem( $adapter );

		// append filespath to the front of the image we are using
		$imagePath = $filesPath . '/' . $image;

		$newImagePath = $filesPath . '/' . $newImageName;

		$tinypngkey = $config['tinypng_apikey'];


		$valid = $this->tinypngValidate( $app, $tinypngkey );


		$optimized = [];

		if ( $valid ) {
			$this->tryOptimization( $app, $imagePath, $newImagePath, $preserveOptions );
			$optimized[] = [
				'compressionCount' => $this->getCompressionCount( $app ),
				'optimizedSize'    => self::bytesToHuman( $filesystem->getSize( $image ) ),
			];
		}

		return new JsonResponse( $optimized );
	}

	public function deleteImage( Application $app, Request $request )
	{
		$boltFilesPath = $app['resources']->getPath( 'filespath' );
		$adapter       = new Local( $boltFilesPath );
		$filesystem    = new Filesystem( $adapter );
		$image         = $request->get( 'image' );

		return new JsonResponse( $filesystem->delete( $image ), 200 );
	}


	/**
	 * @param Application $app
	 *
	 * @return null|string
	 */
	protected function getCompressionCount( Application $app )
	{
		$config     = $this->config;
		$tinypngkey = $config['tinypng_apikey'];

		$validateKey = $this->tinypngValidate( $app, $tinypngkey );

		if ( $validateKey ) {
			$comressionsThisMonth = Tinify\getCompressionCount();
		} else {
			$comressionsThisMonth = 'your api key isn\'t valid';

		}

		return $comressionsThisMonth;
	}

	/**
	 * @param Application $app
	 * @param             $apiKey
	 *
	 * @return bool
	 */
	protected function tinypngValidate( Application $app, $apiKey )
	{

		try {
			Tinify\setKey( $apiKey );
			Tinify\validate();
			// Use the Tinify API client.
		} catch ( Tinify\AccountException $e ) {

			$message = "TinyPNG Account Exception: " . $e->getMessage();
			$app['logger.system']->error( $message, [ 'event' => 'authentication' ] );

			$flash = "There was a problem with your API key or with your API account. Your request could not be authorized. If your compression limit is reached, you can wait until the next calendar month or upgrade your subscription. After verifying your API key and your account status, you can retry the request.";
			$app['logger.flash']->error( 'TinyPNG:: ' . $flash . ' <br/> ' . $e->getMessage() );
			// Verify your API key and account limit.
		} catch ( \Exception $e ) {
			$app['logger.system']->error( $e->getMessage(), [ 'event' => 'exception' ] );
		}

		return true;
	}

	/**
	 * @param Application $app
	 * @param             $image
	 * @param             $newName
	 * @param             $dataToPreserve
	 *
	 * @return array
	 */
	protected function tryOptimization( Application $app, $image, $newName, $dataToPreserve )
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
					$preserved = $source->preserve( "creation" );
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

			$app['logger.system']->error( $message, [ 'event' => 'exception' ] );

			$app['logger.flash']->error( 'The request could not be completed because of a problem with the submitted data: ' . $e->getMessage() );

			// Check your source image and request options.
		} catch ( Tinify\ServerException $e ) {
			$message = "TinyPNG Server Exception: " . $e->getMessage();
			$app['logger.system']->error( $message, [ 'event' => 'exception' ] );

			$flash = "The request could not be completed because of a temporary problem with the Tinify API. It is safe to retry the request after a few minutes. If you see this error repeatedly for a longer period of time, please contact support@tinify.com";

			$app['logger.flash']->error( 'TinyPNG Server Exception: ' . $flash );

			// Temporary issue with the Tinify API.
		} catch ( Tinify\ConnectionException $e ) {

			$message = "TinyPNG Connection Exception: " . $e->getMessage();
			$app['logger.system']->error( $message, [ 'event' => 'exception' ] );

			$flash = "The request could not be sent because there was an issue connecting to the Tinify API. You should verify your network connection. It is safe to retry the request";
			$app['logger.flash']->error( 'TinyPNG Connection Exception: ' . $flash );
			// A network connection error occurred.
		} catch ( \Exception $e ) {
			$app['logger.system']->error( $e->getMessage(), [ 'event' => 'exception' ] );
		}

		return $optimized;
	}

	/**
	 * @param $image
	 * @param $newName
	 *
	 * @return string
	 */
	protected function doRename( $image, $newName )
	{
		$getExt = pathinfo( $image );


		if ( empty( $newName ) ) {
			return $image;
		} else {
			return $newName . '.' . $getExt['extension'];
		}

	}

	/**
	 * @param $data
	 *
	 * @return string
	 * this doesn't work because I mucked up the preserved for all
	 */
	protected function tinyPngPreserve( $data )
	{
		$preserved = '';

		if ( $data === 'all' ) {

			$preserved = '"location", "creation", "copyright"';
		}

		if ( $data === 'location' ) {
			$preserved = "location";
		}

		if ( $data === "creation" ) {
			$preserved = "creation";
		}

		if ( $data === "copyright" ) {
			$preserved = "copyright";
		}

		return $preserved;
	}


	/**
	 * @return array
	 */
	protected function checkAccpetedTypes()
	{

		return [ 'image/jpeg', 'image/png', 'image/gif' ];

	}


	/**
	 * @param $bytes
	 *
	 * @return string
	 */
	public static function bytesToHuman( $bytes )
	{
		$units = [ 'B', 'KB', 'MB', 'GB', 'TB', 'PiB' ];

		for ( $i = 0; $bytes > 1024; $i ++ ) {
			$bytes /= 1024;
		}

		return round( $bytes, 2 ) . ' ' . $units[ $i ];
	}

	public function uploadImage( Application $app, Request $request )
	{
		$config        = $this->config;
		$boltFilesPath = $app['resources']->getPath( 'filespath' );
		$adapter       = new Local( $boltFilesPath );
		$filesystem    = new Filesystem( $adapter );

		$tinypngkey = $config['tinypng_apikey'];

		$valid = $this->tinypngValidate( $app, $tinypngkey );

		$tnypngUpload = new TinyPNGUpload( $app, $this->config );

		$configMethod = isset( $this->config['tinypng_upload']['method'] )
			? $this->config['tinypng_upload']['method']
			: '';

		$resizeMethod = $tnypngUpload->tinyPNGMethod( $configMethod );

		$success = [];


		/**
		 * set up our files. If the request is from XHR (ajax) then we make the file bag get
		 * $request->files instead of $request->files->get("our_file_input_name");
		 */
		if ( $request->isXmlHttpRequest() ) {
			$files = $request->files;
		} else {
			$files = $request->files->get( "tnypng_file" );
		}

		foreach ( $files as $img ) {

			$validImage = $this->validateImage( $app, $img->getRealPath() );

			if ( count( $validImage ) > 0 ) {

				$validateErrors = [];

				foreach ( $validImage as $error ) {

					$validateErrors[] = [
						$img->getClientOriginalName() . ' ' . $error->getMessage()
					];

					return new JsonResponse( $validateErrors, 500 );
				}
			}

			if ( $validImage ) {

				try {
					$fileName   = $img->getClientOriginalName();
					$fileExists = $filesystem->has( $this->normalizeFileName( $fileName ) );

					if ( $fileExists ) {
						$fileParts          = pathinfo( $img->getClientOriginalName() );
						$normalizedFilename = $this->normalizeFileName( $fileName );
						$newName            = $this->renameExisting( $normalizedFilename, $fileParts['extension'] );
					} else {
						$newName = $this->normalizeFileName( $fileName );
					}


					$stream = fopen( $img->getRealPath(), 'r+' );
					$filesystem->writeStream( '' . $newName, $stream );
					if ( is_resource( $stream ) ) {

						if ( ! $request->isXmlHttpRequest() ) {
							$app['logger.flash']
								->info( "{$newName} has been successfully uploaded" );
						}

						fclose( $stream );
					}


					$newImagePath = $boltFilesPath . '/' . $newName;


					if ( $valid ) {
						$tnypngUpload->tinyPNGDoResize( $newImagePath, $resizeMethod );
					}

				} catch ( IOException $e ) {
					$message = "The Directory Is Not Writeable. Please Check Your Filesystem Permissions.";

					$app['logger.system']->error( $message, [ 'event' => 'upload' ] );

					$app['session']
						->getFlashBag()
						->set( 'error', 'TinyPNG:: ' . $message );

					return null;
				}

				$success[] = [
					'name'             => $newName,
					'optimizedSize'    => self::bytesToHuman( $filesystem->getSize( $newName ) ),
					'compressionCount' => $this->getCompressionCount( $app )
				];
			}


		}


		return new JsonResponse( $success );
	}

	/**
	 * @param Application $app
	 * @param Request     $request
	 * @param Form        $form
	 *
	 * @return null|JsonResponse
	 */
//	public function uploadImage( Application $app, Request $request, Form $form )
//	{
//		$config        = $this->config;
//		$boltFilesPath = $app['resources']->getPath( 'filespath' );
//		$adapter       = new Local( $boltFilesPath );
//		$filesystem    = new Filesystem( $adapter );
//
//		$form->handleRequest( $request );
//		$wrongImageType = $form['file']->getErrors();
//
//		$tinypngkey = $config['tinypng_apikey'];
//
//		$valid = $this->tinypngValidate( $app, $tinypngkey );
//
//		$tnypngUpload = new TinyPNGUpload( $app, $this->config );
//
//		$configMethod = isset( $this->config['tinypng_upload']['method'] )
//			? $this->config['tinypng_upload']['method']
//			: '';
//
//		$resizeMethod = $tnypngUpload->tinyPNGMethod( $configMethod );
//
//
//		if ( ! $form->isValid() ) {
//			$app['logger.flash']->error( 'TinyPNG File upload failed:: ' . $wrongImageType );
//
//			return null;
//		}
//
////		$files = $request->files->get( $form->getName() );
////		$files = $files['upload'];
//
//		$files = $request->files;
//
//
//		$uploadOptimized = [];
//
//		// flysystem stream uploads
//		foreach ( $files as $image ) {
//			if ( $image->isValid() ) {
//				try {
//					$fileName   = $image->getClientOriginalName();
//					$fileExists = $filesystem->has( $this->normalizeFileName( $fileName ) );
//
//					if ( $fileExists ) {
//						$fileParts          = pathinfo( $image->getClientOriginalName() );
//						$normalizedFilename = $this->normalizeFileName( $fileName );
//						$newName            = $this->renameExisting( $normalizedFilename, $fileParts['extension'] );
//					} else {
//						$newName = $this->normalizeFileName( $fileName );
//					}
//
//
//					$stream = fopen( $image->getRealPath(), 'r+' );
//					$filesystem->writeStream( '' . $newName, $stream );
//					if ( is_resource( $stream ) ) {
//						$app['logger.flash']
//							->info( "{$newName} has been successfully uploaded" );
//						fclose( $stream );
//					}
//
//
//					$newImagePath = $boltFilesPath . '/' . $newName;
//
//
//					if ( $valid ) {
//						$uploadOptimized = $tnypngUpload->tinyPNGDoResize(
//							$newImagePath, $resizeMethod );
//					}
//
//				} catch ( IOException $e ) {
//					$message = "The Directory Is Not Writeable. Please Check Your Filesystem Permissions.";
//
//					$app['logger.system']->error( $message, [ 'event' => 'upload' ] );
//
//					$app['session']
//						->getFlashBag()
//						->set( 'error', 'TinyPNG:: ' . $message );
//
//					return null;
//				}
//			}
//		}
//
//		return new JsonResponse( $uploadOptimized );
//	}

	/**
	 * @param $filename
	 *
	 * @return string
	 */
	private function normalizeFileName( $filename )
	{
		return trim( preg_replace( '/\s+/', '_', $filename ) );
	}

	/**
	 * @param $normalizedName
	 * @param $extension
	 *
	 * @return string
	 */
	private function renameExisting( $normalizedName, $extension )
	{
		return $normalizedName . '_' . date( "Ymd_" ) . uniqid() . '.' . $extension;
	}


	private function validateImage( Application $app, $image )
	{
		$vConstraints =
			new Assert\All( [
				new Assert\Image( [
					'mimeTypes'        => [
						'image/jpeg',
						'image/png',
						'image/gif'
					],
					'mimeTypesMessage' => 'Images Must Be Either a PNG or JPG / JPEG',
				] )
			] );

		$validateImage = is_array( $image ) ? $image : array( $image );

		return $app['validator']->validate( $validateImage, $vConstraints );
	}

}
