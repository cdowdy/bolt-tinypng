<?php

namespace Bolt\Extension\cdowdy\tinypng;

$autoload = __DIR__ . '/../vendor/autoload.php';
if ( is_file( $autoload ) ) {
	require $autoload;
}


use Bolt\Asset\File\JavaScript;
use Bolt\Asset\File\Stylesheet;
use Bolt\Controller\Zone;
use Bolt\Extension\cdowdy\tinypng\Providers\TinyPNGProvider;
use Bolt\Version as Version;
use Bolt\Extension\cdowdy\tinypng\Controller\TinyPNGBackendController;
use Bolt\Extension\SimpleExtension;
use Bolt\Menu\MenuEntry;


/**
 * TinyPNGExtension extension class.
 *
 * @author Cory Dowdy <cory@corydowdy.com>
 */
class TinyPNGExtension extends SimpleExtension {


	/**
	 * {@inheritdoc}
	 */
	protected function registerAssets() {

		return [
			(new JavaScript('tinypng.optimize.js') )
				->setLate( true )
				->setPriority( 99 )
				->setZone( Zone::BACKEND ),
//			(new Javascript('dropzone.js') )
//				->setLate(true)
//				->setPriority(99)
//				->setZone( Zone::BACKEND ),
			(new Stylesheet('tinypng.styles.css'))
				->setPriority(99)
				->setZone(Zone::BACKEND),
		];
	}


	/**
	 * @return array
	 */
	protected function registerMenuEntries() {
		$menu = new MenuEntry( 'tinypng-menu', 'tinypng/files' );
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

        if ( version_compare( Version::VERSION , '3.3.0', '>=' ) ) {

            return [
                '/extensions/tinypng' => new TinyPNGBackendController($config),
            ];
        } else {
            return [
                '/extend/tinypng' => new TinyPNGBackendController($config),
            ];
        }
	}

	/**
	 * {@inheritdoc}
	 */
	protected function registerTwigPaths() {
		return [ 'templates'=> [ 'position' => 'prepend', 'namespace'=>'tinypng'] ];
	}

    /**
     * @return array
     */
    public function getServiceProviders()
    {
        return [
            $this,
            new TinyPNGProvider( $this->getConfig() ),
        ];
    }
}
