<?php

namespace Bolt\Extension\cdowdy\tinypng;

$autoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($autoload)) {
	require $autoload;
}


use Bolt\Asset\File\JavaScript;
use Bolt\Asset\File\Stylesheet;
use Bolt\Controller\Zone;
use Bolt\Events\StorageEvent;
use Bolt\Events\StorageEvents;
use Bolt\Extension\cdowdy\tinypng\Controller\TinyPNGBackendController;
use Bolt\Extension\SimpleExtension;
use Bolt\Asset\Widget\Widget;
use Bolt\Menu\MenuEntry;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tinify;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;


/**
 * ExtensionName extension class.
 *
 * @author Your Name <you@example.com>
 */
class TinyPNGExtension extends SimpleExtension
{



    /**
     * {@inheritdoc}
     */
    protected function registerAssets()
    {
	    $widgetObj = new \Bolt\Asset\Widget\Widget();
	    $widgetObj
		    ->setZone('backend')
		    ->setLocation('dashboard_aside_top')
		    ->setCallback([$this, 'tinyPNGDashboard']);

	    $asset = new JavaScript();
	    $asset->setFileName('/extensions/vendor/cdowdy/tinypng/web/tinypng.optimize.js')
	          ->setLate(true)
	          ->setPriority(99)
	          ->setAttributes(['defer', 'async'])
	          ->setZone(Zone::BACKEND)
	    ;


	    return [ $widgetObj , $asset];
    }



	/**
	 * @return array
	 */
	protected function registerMenuEntries()
	{
		$menu = new MenuEntry('tinypng-menu', 'tinypng');
		$menu->setLabel('TinyPNG Image Optimization')
		     ->setIcon('fa:image')
		     ->setPermission('settings')
		;

		return [
			$menu,
		];
	}

	/**
	 * @return array
	 * backend controller for optimization and info  page
	 */
	protected function registerBackendControllers()
	{
		$config = $this->getConfig();

		return [
			'/extend/tinypng' => new TinyPNGBackendController($config),
		];
	}


	/**
	 * @param ControllerCollection $collection
	 */
//	protected function registerBackendRoutes( ControllerCollection $collection ) {
//		$collection->match( '/extend/tinypng', [ $this, 'imageOptimization' ] );
//	}


	/**
	 * Handles GET requests on /bolt/extend/image-optimizationand return a template.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function imageOptimization(Request $request)
	{
		$app = $this->getContainer();
		$adapter = new Local($app['resources']->getPath('filespath') );
		$filesystem = new Filesystem($adapter);
		$fileList = $filesystem->listContents(null, true);
		$files = [];

		$vars = [];

		$html = $this->renderTemplate('tinypng.imageoptimization.html.twig', $vars);


		return new \Twig_Markup($html, 'UTF-8');
	}

    /**
     * {@inheritdoc}
     */
    protected function registerTwigPaths()
    {
        return ['templates'];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigFunctions()
    {
	    $options = ['is_safe' => ['html']];
	    $this->getConfig();
	    return [
		    'my_twig_function' => ['myTwigFunction',  $options ],
	    ];

    }

    /**
     * The callback function when {{ my_twig_function() }} is used in a template.
     *
     * @return string
     */
    public function myTwigFunction( $file )
    {
    	$app = $this->getContainer();
	    $config = $this->getConfig();
	    $tinypngkey = $config['tinypng_apikey'];

	    $filesPath = $app['resources']->getpath('filespath');

		Tinify\setKey($tinypngkey);
		$valid = Tinify\validate();

		$fileToOptimize = $filesPath . '/' . $file;

	    $source = \Tinify\fromFile($fileToOptimize);
	    $source->toFile($filesPath . '/' . "optimized-" . $file ) ;

        $context = [
            'filespath' => $filesPath,
	        'file' => $file,
        ];

	    $renderTemplate = $this->renderTemplate('extension.twig', $context);

	    return new \Twig_Markup($renderTemplate, 'UTF-8');
    }

    public function tinyPNGDashboard()
    {
	    $config = $this->getConfig();
	    $tinypngkey = $config['tinypng_apikey'];

	    Tinify\setKey($tinypngkey);
	    $valid = Tinify\validate();

	    if ($valid) {
		    $comressionsThisMonth = Tinify\getCompressionCount();
	    } else {
	    	$comressionsThisMonth = 'your api key isn\'t valid';
	    }

	    $context = [
	    	'compressions' => $comressionsThisMonth,
	    ];

	    $renderTemplate = $this->renderTemplate('dashboard_widget_aside.twig', $context);
	    return new \Twig_Markup($renderTemplate, 'UTF-8');
    }

    // validate tiny png api key
	protected function tinypngValidate()
	{
		$config = $this->getConfig();
		$tinyPNGKey =  $config['tinypng_apikey'];

		try {
			Tinify\setKey($tinyPNGKey);
			Tinify\validate();
			// Use the Tinify API client.
		} catch(\Tinify\AccountException $e) {
			print("The error message is: " . $e->getMessage());
			// Verify your API key and account limit.
		} catch(\Tinify\ClientException $e) {
			// Check your source image and request options.
		} catch(\Tinify\ServerException $e) {
			// Temporary issue with the Tinify API.
		} catch(\Tinify\ConnectionException $e) {
			// A network connection error occurred.
		} catch(Exception $e) {
			// Something else went wrong, unrelated to the Tinify API.
		}
	}
}
