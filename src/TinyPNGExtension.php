<?php

namespace Bolt\Extension\cdowdy\tinypng;

$autoload = __DIR__ . '/../vendor/autoload.php';
if ( is_file( $autoload ) ) {
	require $autoload;
}


use Bolt\Asset\File\JavaScript;

use Bolt\Controller\Zone;

use Bolt\Extension\cdowdy\tinypng\Controller\TinyPNGBackendController;
use Bolt\Extension\SimpleExtension;
use Bolt\Menu\MenuEntry;


/**
 * ExtensionName extension class.
 *
 * @author Your Name <you@example.com>
 */
class TinyPNGExtension extends SimpleExtension {


	/**
	 * {@inheritdoc}
	 */
	protected function registerAssets() {


		$asset = new JavaScript();
		$asset->setFileName( '/extensions/vendor/cdowdy/tinypng/tinypng.optimize.js' )
		      ->setLate( true )
		      ->setPriority( 99 )
		      ->setAttributes( [ 'defer', 'async' ] )
		      ->setZone( Zone::BACKEND );


		return [ $asset ];
	}


	/**
	 * @return array
	 */
	protected function registerMenuEntries() {
		$menu = new MenuEntry( 'tinypng-menu', 'tinypng' );
		$menu->setLabel( 'TinyPNG Image Optimization' )
		     ->setIcon( 'fa:image' )
		     ->setPermission( 'settings' );

		return [
			$menu,
		];
	}

	/**
	 * @return array
	 * backend controller for optimization and info  page
	 */
	protected function registerBackendControllers() {
		$config = $this->getConfig();

		return [
			'/extend/tinypng' => new TinyPNGBackendController( $config ),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function registerTwigPaths() {
		return [ 'templates' ];
	}
}
