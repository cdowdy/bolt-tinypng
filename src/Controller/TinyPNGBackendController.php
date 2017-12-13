<?php

namespace Bolt\Extension\cdowdy\tinypng\Controller;


use Bolt\Extension\cdowdy\tinypng\Helpers\ConfigHelper;
use Bolt\Extension\cdowdy\tinypng\Helpers\FilePathHelper;
use Bolt\Filesystem\Exception\IOException;


use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints as Assert;


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
		$ctr->match( '/files/{directory}', [ $this, 'allImages' ] )
		    ->assert( "directory", '.+' )
		    ->value( "directory", "index" )
		    ->bind( 'tinypng-all-images' );

		$ctr->post( '/optimize/{directory}', [ $this, 'optimizeImage' ] )
		    ->assert( "directory", '.+' )
		    ->value( "directory", "index" )
		    ->bind( 'tinypng-optimize' );

		$ctr->post( '/rename/{directory}', [ $this, 'renameOptimize' ] )
		    ->assert( "directory", '.+' )
		    ->value( "directory", "index" )
		    ->bind( 'tinypng-rename' );

        $ctr->post( '/batch/optimize/{directory}', [ $this, 'batchOptimize' ] )
            ->assert( "directory", '.+' )
            ->value( "directory", "index" )
            ->bind( 'tinypng-batch-optimize' );

		$ctr->post( '/upload/{directory}', [ $this, 'uploadImage' ] )
		    ->assert( "directory", '.+' )
		    ->value( "directory", "index" )
		    ->bind( 'tinypng-upload-images' );

		$ctr->post( '/delete', [ $this, 'deleteImage' ] )
		    ->bind( 'tinypng-delete-image' );

		$ctr->post( '/create/{directory}', [ $this, 'createDirectory' ] )
		    ->assert( "directory", '.+' )
		    ->value( "directory", "index" )
		    ->bind( 'tinypng-create-directory' );

		$ctr->post( '/directory-delete/{directory}', [$this, 'deleteDirectory'] )
            ->assert( "directory", '.+' )
            ->value( "directory", "index")
            ->bind('tinypng-delete-directory');


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

        // axios sends data differently than Jquery Ajax did
        // Jquery defaults to x-www-form-urlencoded while Axios / fetch send it as actual JSON
        // SO we'll need to decode the request to get the data and use Axios' 'data' key as our key to then
        // grab our 'image' with the associated file name
        // see https://silex.symfony.com/doc/2.0/cookbook/json_request_body.html#parsing-the-request-body
        if ( 0 === strpos( $request->headers->get( 'Content-Type' ), 'application/json' ) ) {
            $requestData = json_decode( $request->getContent( 'data' ), true );

            $request->request->replace( is_array( $requestData ) ? $requestData : [] );
        }

		return null;
	}


	/**
	 * @param Application $app
	 *
	 * @param Request     $request
	 *
	 * @param             $directory
	 *
	 * @return mixed
	 */
	public function allImages( Application $app, Request $request, $directory )
	{
		$filesystem = $this->fsSetup( $app );

		// recursively get all the files under '/files/'
		// don't worry about folder structure just dump em all out :)

		if ( $directory == 'index' ) {
			$fileList = $filesystem->listContents( null, false );
		} else {
			$fileList = $filesystem->listContents( $directory, false );
		}

		$paths = $filesystem->listContents( null );

		$dirs = $this->getAllDirectories( $app, $paths );


		if ( $request->isMethod( 'POST' ) ) {
			$this->uploadImage( $app, $request, $directory );
		}

		$uploadMethod = isset( $this->config['tinypng_upload']['method'] )
			? $this->config['tinypng_upload']['method']
			: '';

		$configHelper = ( new ConfigHelper( $app, $this->config ) )
			->setTnypngAPIKey( $this->config['tinypng_apikey'] )
			->setUploadMethod( $uploadMethod );


		$methods = $app['tinypng.upload']->tinyPNGMethod( $configHelper->getUploadMethod() );

		$checkW = $app['tinypng.upload']->checkForWidthHeights( 'width' );
		$checkH = $app['tinypng.upload']->checkForWidthHeights( 'height' );

		// context to render in our twig template
		$context = [
			'noKey'              => empty( $configHelper->getTnypngAPIKey() ),
			'tinyPNG_files'      => json_encode( $this->getAllFiles( $app, $fileList ) ),
			'tnypng_directories' => json_encode( $this->getAllDirectories( $app, $paths ) ),
			'compressionCount'   => $app['tinypng.optimize']->getCompressionCount(),
			'uploadMethod'       => $methods,
			'maxWidth'           => $checkW,
			'maxHeight'          => $checkH,
			'directory'          => $directory,
		];

		return $app['twig']->render( '@tinypng/tinypng.imageoptimization.html.twig', $context );
	}


    /**
     * @param $app
     * @param $fileList
     * @return array
     */
    protected function getAllDirectories( $app, $fileList )
    {
        $filesystem   = $this->fsSetup($app);
        $urlGenerator = $app[ 'url_generator' ];

        $files = [];

        foreach ( $fileList as $object ) {
            if ( $object[ 'type' ] == 'dir'
                && !preg_match_all('/.cache/i', $object[ 'dirname' ])
                && !preg_match_all('/.cache/i', $object[ 'basename' ])
            ) {

                $listPaths = $filesystem->listContents($object[ 'path' ], true);

                $files[] = [
                    'path'         => $object[ 'path' ],
                    'route'        => $urlGenerator->generate('tinypng-all-images', [ 'directory' => $object[ 'path' ] ]),
                    'subdirectory' => array_filter($this->listFileSystemPaths($app, $listPaths))
                ];
            }

        }

        return $files;

    }

    /**
     * @param $app
     * @param $paths
     * @return array
     */
	protected function listFileSystemPaths( $app, $paths )
	{
		$pathsList = [];

        $urlGenerator = $app['url_generator'];

        foreach ( $paths as $object ) {
            if ( $object[ 'type' ] == 'dir'
                && !preg_match_all( '/.cache/i', $object[ 'dirname' ] )
                && !preg_match_all( '/.cache/i', $object[ 'basename' ] )
            ) {
                $pathsList[] = [
                    'path'  => $object[ 'path' ],
                    'route' => $urlGenerator->generate('tinypng-all-images', [ 'directory' => $object[ 'path' ] ])
                ];
            }
        }

		return $pathsList;

	}


    /**
     * @param Application $app
     * @param $fileList
     * @return array
     */
	protected function getAllFiles( Application $app, $fileList )
	{
		$filesystem    = $this->fsSetup( $app );

        $boltFilesPath = (new FilePathHelper( $app ) )->boltFilesPath() ;
		$expectedMimes = $this->checkAccpetedTypes();
		$files         = [];

		foreach ( $fileList as $object ) {

			// we only want "files" here so anything else in the files directory can be "discarded"
			// we'll also skip over if there is a ".cache" directory like from my betterthumbs extension
			// finally we'll make sure we are only dealing with jpg/png files
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

		// request variables to get from the posted data
		$image           = $request->request->get( 'image' );
		$preserveOptions = $request->request->get( 'preserve' );

		// get bolts filepath - can be changed by the user
        $filesPath = (new FilePathHelper( $app ) )->boltFilesPath() ;


		// append filespath to the front of the image we are using
		$imagePath = $filesPath . '/' . $image;

		$valid = $app['tinypng.optimize']->tinypngValidate();

		$optimized = [];
        if ( $valid ) {
            $app[ 'tinypng.optimize' ]->tryOptimization($imagePath, '', $preserveOptions);

            $optimized[] = $this->normalizedResponse($app, $image, $imagePath );
        }


		return new JsonResponse( $optimized );
	}


    /**
     * @param Application $app
     * @param Request     $request
     * @param             $directory
     * @return JsonResponse
     *
     * batch optimize the images and return the file list and the compression count
     */
    public function batchOptimize( Application $app, Request $request, $directory )
    {

        $images          = $request->request->get('images');
        $preserveOptions = $request->request->get('preserve');

        $filesPath = ( new FilePathHelper($app) )->boltFilesPath();
        $valid     = $app[ 'tinypng.optimize' ]->tinypngValidate();

        $filesystem = $this->fsSetup($app);


        if ( $directory == 'index' ) {
            $fileList = $filesystem->listContents(null, false);
        } else {
            $fileList = $filesystem->listContents($directory, false);
        }


        foreach ( $images as $image ) {
            if ( $valid ) {
                // append filespath to the front of the image we are using
                $imagePath = $filesPath . '/' . $image;
                $app[ 'tinypng.optimize' ]->tryOptimization($imagePath, '', $preserveOptions);

            }
        }

        $optimized = [
            'fileList'         => $this->getAllFiles($app, $fileList),
            'compressionCount' => $app[ 'tinypng.optimize' ]->getCompressionCount(),
        ];

        return new JsonResponse($optimized);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @param             $directory
     *
     * @return JsonResponse
     */
    public function renameOptimize( Application $app, Request $request, $directory )
    {

        // request variables to get from the posted data
        $image           = $request->request->get('image');
        $newImageName    = $request->request->get('newName');
        $preserveOptions = $request->request->get('preserve');


        // get bolts filepath - can be changed by the user
        $filesPath = ( new FilePathHelper($app) )->boltFilesPath();

        // append filespath to the front of the image we are using
        $imageToOptimize = $filesPath . '/' . $image;


        if ( $directory == 'index' ) {
            $newImagePath = $filesPath . '/' . $newImageName;
            $renamedImage = $newImageName;
        } else {
            $newImagePath = $filesPath . '/' . $directory . '/' . $newImageName;
            $renamedImage = $directory . '/' . $newImageName;
        }


        // validate the tinypng/tinyjpg api key
        $valid = $app[ 'tinypng.optimize' ]->tinypngValidate();


        $optimized = [];

        if ( $valid ) {
            $app[ 'tinypng.optimize' ]->tryOptimization($imageToOptimize, $newImagePath, $preserveOptions);

            $optimizedImageExt    = pathinfo($imageToOptimize, PATHINFO_EXTENSION);
            $newImagePathWithExt  = $newImagePath . '.' . $optimizedImageExt;
            $renamedWithExtension = $renamedImage . '.' . $optimizedImageExt;

            $optimized[] = $this->normalizedResponse($app, $renamedWithExtension, $newImagePathWithExt);
        }

        return new JsonResponse($optimized);
    }


    /**
     * @param Application $app
     * @param Request $request
     * @return JsonResponse
     */
	public function deleteImage( Application $app, Request $request )
	{
		$filesystem = $this->fsSetup( $app );
		$image      = $request->request->get( 'image' );

		return new JsonResponse( $filesystem->delete( $image ), 200 );
	}


    /**
     * @param Application $app
     * @return Filesystem
     */
	private function fsSetup( Application $app )
	{
	    // for bolt's new filesystem since $app['resources'] and getPath() are deprecated in 3.3+
        // and will be removed in 4.0

        $boltFilesPath = (new FilePathHelper( $app ) )->boltFilesPath() ;

		$adapter       = new Local( $boltFilesPath );
		$filesystem    = new Filesystem( $adapter );

		return $filesystem;
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


    /**
     * @param Application $app
     * @param             $image
     * @param             $imagePath
     * @return array
     */
	public  function normalizedResponse(Application $app, $image, $imagePath)
    {
        $filesystem = $this->fsSetup( $app );
        $imageWidthHeight = getimagesize( $imagePath );

        return [

            'filename'      => pathinfo($imagePath, PATHINFO_BASENAME),
            'optimizedSize' => self::bytesToHuman($filesystem->getSize($image)),
            'imageWidth'    => $imageWidthHeight[ 0 ],
            'imageHeight'   => $imageWidthHeight[ 1 ],
            'imagePath'     => $filesystem->getAdapter()->getMetadata($image)[ 'path' ],
            'located'       => pathinfo($image, PATHINFO_DIRNAME),

            'compressionCount' => $app[ 'tinypng.optimize' ]->getCompressionCount(),
        ];

    }


    /**
     * @param Application $app
     * @param Request $request
     * @param $directory
     * @return null|JsonResponse
     */
	public function uploadImage( Application $app, Request $request, $directory )
	{

		if ( $directory == 'index' ) {

			$uploadDir = null;
		} else {

			$uploadDir = $directory . '/';
		}

        $boltFilesPath = (new FilePathHelper( $app ) )->boltFilesPath() ;
		$filesystem = $this->fsSetup( $app );

		$valid = $app['tinypng.optimize']->tinypngValidate();

		$configMethod = isset( $this->config['tinypng_upload']['method'] )
			? $this->config['tinypng_upload']['method']
			: '';

		$resizeMethod = $app['tinypng.upload']->tinyPNGMethod( $configMethod );

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
					$fileName   = $uploadDir . $img->getClientOriginalName();
					$fileExists = $filesystem->has( $this->normalizeFileName( $fileName ) );

					if ( $fileExists ) {
						$fileParts          = pathinfo( $fileName );
						$normalizedFilename = $this->normalizeFileName( $fileName );
						$newName            = $this->renameExisting( $normalizedFilename, $uploadDir,
							$fileParts['extension'] );
					} else {
						$newName = $this->normalizeFileName( $fileName );
					}


					$stream = fopen( $img->getRealPath(), 'r+' );
					$filesystem->writeStream( $newName, $stream );
					if ( is_resource( $stream ) ) {

						if ( ! $request->isXmlHttpRequest() ) {
							$app['logger.flash']
								->info( "{$newName} has been successfully uploaded" );
						}

						fclose( $stream );
					}


					$newImagePath = $boltFilesPath . '/' . $newName;


					if ( $valid ) {
                        $app['tinypng.upload']->tinyPNGDoResize( $newImagePath, $resizeMethod );
					}

				} catch ( IOException $e ) {
					$message = "The Directory Is Not Writeable. Please Check Your Filesystem Permissions.";

					$app['logger.system']->error( $message, [ 'event' => 'upload' ] );

					$app['session']
						->getFlashBag()
						->set( 'error', 'TinyPNG:: ' . $message );

					return null;
				}

                $imageWidthHeight = getimagesize( $boltFilesPath . '/' . $newName );
                $width            = $imageWidthHeight[0];
                $height           = $imageWidthHeight[1];

                $fileNameOnly = pathinfo( $boltFilesPath . '/' . $newName );

                $success[] = [
                    'filename'         => $fileNameOnly[ 'basename' ],
                    'imagePath'        => $newName,
                    'imageWidth'       => $width,
                    'imageHeight'      => $height,
                    'optimizedSize'    => self::bytesToHuman($filesystem->getSize($newName)),
                    'compressionCount' => $app[ 'tinypng.optimize' ]->getCompressionCount()
                ];
			}


		}


		return new JsonResponse( $success );
	}


	/**
	 * @param $filename
	 *
	 * @return string
	 */
	private function normalizeFileName( $filename )
	{
		$pathParts      = pathinfo( $filename );
		$normalized     = trim( preg_replace( '/\s+/', '_', $pathParts['filename'] ) );
		$normalizedName = $pathParts['dirname'] . '/' . $normalized . '.' . $pathParts['extension'];

		return $normalizedName;
	}

	/**
	 * @param $normalizedName
	 * @param $extension
	 *
	 * @return string
	 */
	private function renameExisting( $normalizedName, $directory, $extension )
	{
		$parts    = pathinfo( $normalizedName );
		$fileName = isset( $directory )
			? $directory . $parts['filename']
			: $parts['filename'];

		return $fileName . '_' . date( "Ymd_" ) . uniqid() . '.' . $extension;
	}


    /**
     * @param Application $app
     * @param Request $request
     * @param $directory
     * @return null|RedirectResponse
     */
	public function createDirectory( Application $app, Request $request, $directory )
	{
		$filesystem = $this->fsSetup( $app );
		$newDirName = $request->request->get( "tinypngNewDirectory" );

		if ( $directory == 'index' ) {

			$currentDir = '';
		} else {

			$currentDir = $directory . '/';
		}

		try {
			$filesystem->createDir( $currentDir . $newDirName );
			$app['session']
				->getFlashbag()
				->set( 'success', "{$newDirName} Successfully Created!" );

		} catch ( IOException $e ) {
			$message = "Cannot Create Directory. Please Check Your Filesystem Permissions.";

			$app['logger.system']->error( $message, [ 'event' => 'exception' ] );

			$app['session']
				->getFlashBag()
				->set( 'error', 'TinyPNG:: ' . $message );

			return null;
		}

		$urlGenerator = $app['url_generator'];

		return new RedirectResponse( $urlGenerator->generate( 'tinypng-all-images', [ 'directory' => $directory ] ) );
	}

    /**
     * @param Application $app
     * @param Request $request
     * @param $directory
     * @return null|RedirectResponse
     */
	public function deleteDirectory( Application $app, Request $request, $directory )
    {
        $filesystem = $this->fsSetup( $app );
        $dirToRemove = $request->request->get( "tinypngDeleteDirectory" );


        try {
            $filesystem->deleteDir( $dirToRemove );
            $app['session']
                ->getFlashbag()
                ->set( 'success', "{$dirToRemove} Successfully Deleted/Removed!" );

        } catch ( IOException $e ) {
            $message = "Cannot Delete Directory. Please Check Your Filesystem Permissions.";

            $app['logger.system']->error( $message, [ 'event' => 'exception' ] );

            $app['session']
                ->getFlashBag()
                ->set( 'error', 'TinyPNG:: ' . $message );

            return null;
        }

        $urlGenerator = $app['url_generator'];

        return new RedirectResponse( $urlGenerator->generate( 'tinypng-all-images', [ 'directory' => $directory ] ) );

    }


    /**
     * @param Application $app
     * @param $image
     * @return mixed
     */
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
