<?php

namespace Bolt\Extension\cdowdy\tinypng\Controller;

//$autoload = __DIR__ . '/../vendor/autoload.php';
//if (is_file($autoload)) {
//	require $autoload;
//}

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tinify;


/**
 * Controller class.
 *
 * @author Your Name <you@example.com>
 */
class TinyPNGBackendController implements ControllerProviderInterface
{
    /** @var array The extension's configuration parameters */
    private $config;

    /**
     * Initiate the controller with Bolt Application instance and extension config.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Specify which method handles which route.
     *
     * Base route/path is '/example/url'
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        /** @var $ctr \Silex\ControllerCollection */
        $ctr = $app['controllers_factory'];

        // /example/url/in/controller
        $ctr->get('/', [$this, 'allImages'])
            ->bind('tinypng-all-images');

        $ctr->post('/optimize', [$this, 'optimizeImage'])
	        ->bind('tinypng-optimize');

	    $ctr->post('/optimize/rename', [$this, 'renameOptimize'])
	        ->bind('tinypng-rename');

	    $ctr->before([$this, 'before']);

        return $ctr;
    }

	/**
	 * @param Request $request
	 * @param Application $app
	 * @return null|RedirectResponse
	 */
	public function before(Request $request, Application $app)
	{
		if (!$app['users']->isAllowed('dashboard')) {
			/** @var UrlGeneratorInterface $generator */
			$generator = $app['url_generator'];
			return new RedirectResponse($generator->generate('dashboard'), Response::HTTP_SEE_OTHER);
		}
		return null;
	}

	/**
	 * @param Application $app
	 *
	 * @return mixed
	 */
    public function allImages(Application $app)
    {
	    $adapter = new Local($app['resources']->getPath('filespath') );
	    $filesystem = new Filesystem($adapter);
	    $fileList = $filesystem->listContents(null, true);

	    $expectedMimes = $this->checkAccpetedTypes();
	    $files = [];

		$compressionCount = $this->getCompressionCount($app);


	    foreach ( $fileList as $object  ) {

		    if ($object['type'] == 'file'
		        && !preg_match_all('/^.cache\//i', $object['dirname'])
		        && in_array(strtolower($filesystem->getMimetype($object['path'])), $expectedMimes ) ) {

			    $files[] = [
				    'filename' => $object['basename'],
				    'located' => $object['dirname'],
				    'imagePath' => $object['path'],
				    'mimeType' => $filesystem->getMimetype($object['path']),
					'filesize' => self::bytesToHuman($filesystem->getSize($object['path'])),
			    ];
		    }
	    }
	    $noKey = empty($this->config['tinypng_apikey']);
	    $context = [
	    	'noKey' => $noKey,
	    	'tinyPNG_files' => $files,
		    'compressionCount' => $compressionCount,
	    ];

	    return $app['twig']->render('tinypng.imageoptimization.html.twig', $context);
    }

	/**
	 * @param Application $app
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function optimizeImage(Application $app, Request $request)
    {
    	$config = $this->config;

    	// request variables to get from the posted data
    	$image = $request->get('image');
    	$preserveOptions = $request->get('preserve');

    	// get bolts filepath - can be changed by the user
	    $filesPath = $app['resources']->getpath('filespath');

	    // append filespath to the front of the image we are using
	    $imagePath = $filesPath . '/' . $image;

	    $tinypngkey = $config['tinypng_apikey'];



	    $valid = $this->tinypngValidate($app, $tinypngkey);

		$optimized = [];
		if ($valid) {
			$optimized = $this->tryOptimization($app, $imagePath,'', $preserveOptions);
		}


	    return new JsonResponse($optimized);
    }

	/**
	 * @param Application $app
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
    public function renameOptimize(Application $app, Request $request)
    {
	    $config = $this->config;

	    // request variables to get from the posted data
	    $image = $request->get('image');
	    $newImageName = $request->get('newName');
	    $preserveOptions = $request->get('preserve');


	    // get bolts filepath - can be changed by the user
	    $filesPath = $app['resources']->getpath('filespath');

	    // append filespath to the front of the image we are using
	    $imagePath = $filesPath . '/' . $image;

	    $newImagePath = $filesPath . '/' . $newImageName;

	    $tinypngkey = $config['tinypng_apikey'];


	    $valid = $this->tinypngValidate($app, $tinypngkey);


	    $optimized = [];
	    if ($valid) {
		    $optimized = $this->tryOptimization($app, $imagePath, $newImagePath, $preserveOptions);
	    }

	    return new JsonResponse($optimized);
    }


	/**
	 * @param Application $app
	 *
	 * @return null|string
	 */
    protected function getCompressionCount(Application $app)
    {
	    $config = $this->config;
	    $tinypngkey = $config['tinypng_apikey'];

	    $validateKey = $this->tinypngValidate($app, $tinypngkey);

	    if ($validateKey) {
		    $comressionsThisMonth = Tinify\getCompressionCount();
	    } else {
		    $comressionsThisMonth = 'your api key isn\'t valid';

	    }

	    return $comressionsThisMonth;
    }

	/**
	 * @param Application $app
	 * @param $apiKey
	 *
	 * @return bool
	 */
	protected function tinypngValidate(Application $app, $apiKey)
	{

		$config = $this->config;


		try {
			Tinify\setKey($apiKey);
			Tinify\validate();
			// Use the Tinify API client.
		} catch(Tinify\AccountException $e) {

			$message = "TinyPNG Account Exception: " . $e->getMessage();
			$app['logger.system']->error($message, ['event' => 'authentication']);

			$flash = "There was a problem with your API key or with your API account. Your request could not be authorized. If your compression limit is reached, you can wait until the next calendar month or upgrade your subscription. After verifying your API key and your account status, you can retry the request.";
			$app['session']->getFlashBag()
			               ->set('error', 'TinyPNG:: ' . $flash . ' <br/> '  . $e->getMessage());
			// Verify your API key and account limit.
		} catch(\Exception $e) {
			$app['logger.system']->error($e->getMessage(), ['event' => 'exception']);
		}

		return TRUE;
	}

	/**
	 * @param Application $app
	 * @param $image
	 * @param $newName
	 * @param $dataToPreserve
	 *
	 * @return array
	 */
	protected function tryOptimization(Application $app, $image, $newName, $dataToPreserve)
	{
		$optimized = [];
		$imagename = $this->doRename($image, $newName);

		try {
			$source = \Tinify\fromFile($image);

			if ($dataToPreserve !== 'none') {

				if( $dataToPreserve === 'location' ) {
					$preserved = $source->preserve("location");

				} elseif ($dataToPreserve === 'creation'){
					$preserved = $source->preserve("creation");

				} elseif ($dataToPreserve === 'copyright') {
					$preserved = $source->preserve("creation");
				} else {
					$preserved = $source->preserve("location", "creation", "copyright");
				}

				$optimized[] = $preserved->toFile($imagename);
			} else {
				$optimized[] =  $source->toFile($imagename);
			}

			// Use the Tinify API client.
		} catch(Tinify\ClientException $e) {

			$message = "TinyPNG Client Exception: " . $e->getMessage();

			$app['logger.system']->error($message, ['event' => 'exception']);

			$app['session']->getFlashBag()
			               ->set('error', 'The request could not be completed because of a problem with the submitted data: ' . $e->getMessage());

			// Check your source image and request options.
		} catch(Tinify\ServerException $e) {
			$message = "TinyPNG Server Exception: " . $e->getMessage();
			$app['logger.system']->error($message, ['event' => 'exception']);

			$flash = "The request could not be completed because of a temporary problem with the Tinify API. It is safe to retry the request after a few minutes. If you see this error repeatedly for a longer period of time, please contact support@tinify.com";

			$app['session']->getFlashBag()->set('error', 'TinyPNG Server Exception: ' .  $flash );

			// Temporary issue with the Tinify API.
		} catch(Tinify\ConnectionException $e) {

			$message = "TinyPNG Connection Exception: " . $e->getMessage();
			$app['logger.system']->error($message, ['event' => 'exception']);

			$flash = "The request could not be sent because there was an issue connecting to the Tinify API. You should verify your network connection. It is safe to retry the request";
			$app['session']->getFlashBag()->set('error', 'TinyPNG Connection Exception: ' .  $flash );
			// A network connection error occurred.
		} catch(\Exception $e) {
			$app['logger.system']->error($e->getMessage(), ['event' => 'exception']);
		}

		return $optimized;
	}

	/**
	 * @param $image
	 * @param $newName
	 *
	 * @return string
	 */
	protected function doRename($image, $newName)
	{
		$getExt = pathinfo($image);


		if (empty($newName) ) {
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
	protected function tinyPngPreserve($data)
	{
		$preserved = '';

		if ($data === 'all') {

			$preserved =  '"location", "creation", "copyright"';
		}

		if ($data === 'location' ) {
			$preserved = "location";
		}

		if ($data === "creation" ) {
			$preserved = "creation";
		}

		if ($data === "copyright") {
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
	public static function bytesToHuman($bytes)
	{
		$units = ['B', 'KB', 'MB', 'GB', 'TB', 'PiB'];

		for ($i = 0; $bytes > 1024; $i++) {
			$bytes /= 1024;
		}

		return round($bytes, 2) . ' ' . $units[$i];
	}


}
