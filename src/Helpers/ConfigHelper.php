<?php

namespace Bolt\Extension\cdowdy\tinypng\Helpers;

use Silex;

/**
 * Class ConfigHelper
 * @package Bolt\Extension\cdowdy\tinypng\Helpers
 */
class ConfigHelper {

	/**
	 * @var array
	 */
	protected $_extensionConfig;

	/**
	 * @var
	 */
	protected $_uploadMethod;

	/**
	 * @var
	 */
	protected $_uploadMaxWidth;

	/**
	 * @var
	 */
	protected $_uploadMaxHeight;

	/**
	 * @var array
	 */
	protected $_metadata = [];

	/**
	 * @var
	 */
	protected $_tnypngAPIKey;


	/**
	 * ConfigHelper constructor.
	 *
	 * @param Silex\Application $app
	 * @param array             $_extensionConfig
	 */
	public function __construct( Silex\Application $app, array $_extensionConfig )
	{
		$this->app              = $app;
		$this->_extensionConfig = $_extensionConfig;
	}


	/**
	 * @return mixed
	 */
	public function getUploadMethod()
	{
		return $this->_uploadMethod;
	}


	/**
	 * @param mixed $uploadMethod
	 *
	 * @return ConfigHelper
	 */
	public function setUploadMethod( $uploadMethod )
	{
		$this->_uploadMethod = isset( $uploadMethod )
			? $uploadMethod
			: '';

		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getUploadMaxWidth()
	{
		return $this->_uploadMaxWidth;
	}


	/**
	 * @param     $uploadMaxWidth
	 * @param int $default
	 *
	 * @return $this
	 */
	public function setUploadMaxWidth( $uploadMaxWidth, $default = 1000 )
	{

		if ( ! is_numeric( $uploadMaxWidth ) ) {
			$uploadMaxWidth = empty( $this->_extensionConfig['tinypng_upload']['width'] )
				? $default
				: $this->_extensionConfig['tinypng_upload']['width'];
		}

		$this->_uploadMaxWidth = $uploadMaxWidth;

		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getUploadMaxHeight()
	{
		return $this->_uploadMaxHeight;
	}

	/**
	 * @param     $uploadMaxHeight
	 * @param int $default
	 *
	 * @return $this
	 */
	public function setUploadMaxHeight( $uploadMaxHeight, $default = 1000 )
	{

		if ( ! is_numeric( $uploadMaxHeight ) ) {
			$uploadMaxHeight = empty( $this->_extensionConfig['tinypng_upload']['height'] )
				? $default
				: $this->_extensionConfig['tinypng_upload']['height'];
		}

		$this->_uploadMaxHeight = $uploadMaxHeight;

		return $this;

	}


	/**
	 * @return array
	 */
	public function getMetadata(): array
	{
		return $this->_metadata;
	}


	/**
	 * @param array $metadata
	 *
	 * @return ConfigHelper
	 */
	public function setMetadata( array $metadata ): ConfigHelper
	{
		$this->_metadata = $metadata;

		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getTnypngAPIKey()
	{
		return $this->_tnypngAPIKey;
	}

	/**
	 * @param mixed $tnypngAPIKey
	 *
	 * @return ConfigHelper
	 */
	public function setTnypngAPIKey( $tnypngAPIKey )
	{
		$this->_tnypngAPIKey = $tnypngAPIKey;

		return $this;
	}


}