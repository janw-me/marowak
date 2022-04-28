<?php

namespace Marowak\Helper;

use Mustangostang\Spyc;

/**
 * Class URL
 *
 * @package Marowak\Helper
 */
class URL {

	/**
	 * @param null|string $path The path ot check for the url.
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function getLocalUrl( $path = null ) {
		$dir = Paths::getProjectRoot( $path );

		return basename( $dir ) . '.test';
	}

	/**
	 * @param null|string $path The path ot check for the url.
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function getLiveUrl( $path = null ) {
		$dir = Paths::getProjectRoot( $path );

		return basename( $dir ) . '.nl';
	}
}