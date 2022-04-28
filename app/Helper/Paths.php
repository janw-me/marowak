<?php

namespace Marowak\Helper;

/**
 * Class Paths
 *
 * @package Marowak\Helper
 */
class Paths {

	/**
	 * Try to find the path to the .idea folder that PHPstorm uses.
	 *
	 * @param false|null|string $path
	 *
	 * @return string
	 * @throws \Exception
	 */
	static public function getIdea( $path ): string {
		if ( empty( $path ) ) {
			$path = getcwd(); // Default to current working directory
		}
		if ( ! is_dir( $path ) ) {
			throw new \Exception( "Invalid directory '{$path}'" );
		}
		$path = rtrim( $path, "/" ) . '/'; // force trailing slash.

		if ( ! is_dir( "$path.idea" ) ) {
			$parent_directory = dirname( $path );
			if ( '/' === $parent_directory ) {
				throw new \Exception( "Cannot find an .idea folder in the path." );
			}

			return self::getIdea( $parent_directory );
		}

		return "$path.idea";
	}
}