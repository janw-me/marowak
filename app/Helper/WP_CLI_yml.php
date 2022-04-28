<?php

namespace Marowak\Helper;

use Mustangostang\Spyc;

/**
 * Class WPcli_yml
 *
 * @package Marowak\Helper
 */
class WP_CLI_yml {

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var array
	 */
	protected $yml = array();

	/**
	 * @var Spyc
	 */
	protected $spyc;

	public function __construct( string $path ) {
		$this->path = Paths::getWpcliYml( $path );
		$this->spyc = new Spyc();

		if ( is_readable( $this->path ) ) {
			$this->yml = $this->spyc->loadFile( $this->path );
		}
	}

	/**
	 * Set a key in the yml data.
	 *
	 * @param string      $key
	 * @param string      $value
	 * @param string|null $alias
	 *
	 * @return $this
	 */
	public function setVar( string $key, string $value, string $alias = null ) {
		if ( ! empty( $alias ) ) {
			$this->yml[ $alias ][ $key ] = $value;
		} else {
			$this->yml[ $key ] = $value;
		}

		return $this;
	}

	/**
	 * Get a value form the yml data.
	 *
	 * @param string      $key
	 * @param string|null $alias
	 *
	 * @return string
	 */
	public function getVar( string $key, string $alias = null ) {
		if ( ! empty( $alias ) ) {
			return $this->yml[ $alias ][ $key ];
		}

		return $this->yml[ $key ];
	}

	/**
	 * Save the data to the file.
	 */
	public function saveData() {
		file_put_contents( $this->path, $this->getData(true) );
	}

	/**
	 * Get the yml data.
	 *
	 * @param bool $yml_format Format as yml or flat array.
	 *
	 * @return array|string
	 */
	public function getData( $yml_format = false ) {
		if ( ! $yml_format ) {
			return $this->yml;
		}

		return $this->spyc->dump( $this->yml, 2, 0 );
	}

}