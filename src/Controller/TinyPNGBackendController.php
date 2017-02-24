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
		$compressionCount = $this->getCompressionCount();


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
	    $context = [
	    	'tinyPNG_files' => $files,
		    'compressionCount' => $compressionCount,
	    ];

	    return $app['twig']->render('tinypng.imageoptimization.html.twig', $context);
    }

    public function optimizeImage(Application $app, Request $request)
    {
    	$config = $this->config;
    	$image = $request->get('image');
	    $filesPath = $app['resources']->getpath('filespath');
	    $imagePath = $filesPath . '/' . $image;

	    $tinypngkey = $config['tinypng_apikey'];

	    Tinify\setKey($tinypngkey);
	    $valid = Tinify\validate();

	    $optimized = [];
	    if ($valid) {
		    $source = \Tinify\fromFile($imagePath);
		    $optimized[] =  $source->toFile($imagePath);
	    }

	    return new JsonResponse($optimized);
    }

    protected function getCompressionCount()
    {
	    $config = $this->config;
	    $tinypngkey = $config['tinypng_apikey'];

	    Tinify\setKey($tinypngkey);
	    $valid = Tinify\validate();

	    if ($valid) {
		    $comressionsThisMonth = Tinify\getCompressionCount();
	    } else {
		    $comressionsThisMonth = 'your api key isn\'t valid';
	    }

	    return $comressionsThisMonth;
    }


	/**
	 * @return array
	 */
	protected function checkAccpetedTypes()
	{
		$acceptedTypes = '';

		$gdAccepted = [ 'image/jpeg', 'image/png', 'image/gif' ];

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
