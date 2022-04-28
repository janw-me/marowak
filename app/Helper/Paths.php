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
	 * @param string|null $path the path to check.
	 *
	 * @return string path with training slash.
	 * @throws \Exception
	 */
	static public function getIdea( $path = null ): string {
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

	/**
	 * Try top find the project root directory.
	 *
	 * @param string|null $path the path to find the project.
	 *
	 * @return string path with training slash.
	 * @throws \Exception
	 */
	public static function getProjectRoot( $path = null ) {
		return rtrim( dirname( self::getIdea( $path ) ), "/" ) . '/';
	}

	/**
	 * @param string|null $path The path to find a wp directory in.
	 *
	 * @return string|void
	 * @throws \Exception
	 */
	public static function getWpDir( $path = null ) {
		$project_root = self::getProjectRoot( $path );
		if ( file_exists( "{$project_root}app/public/wp-load.php" ) ) {
			return "{$project_root}app/public/";
		}
		if ( file_exists( "{$project_root}app/public/wp/wp-load.php" ) ) {
			return "{$project_root}app/public/wp/";
		}
		if ( file_exists( "{$project_root}app/public_html/wp-load.php" ) ) {
			return "{$project_root}app/public_html/";
		}
		if ( file_exists( "{$project_root}app/public_html/wp/wp-load.php" ) ) {
			return "{$project_root}app/public_html/wp/";
		}

		throw new \Exception( 'Can\'t find a WordPress folder.' );
	}

	/**
	 *
	 * @param null $path The path to find the WP project.
	 *
	 * @return string|null null if not found.
	 * @throws \Exception
	 */
	public static function getWpcliYml( $path = null ) {
		if ( 'wp-cli.yml' === basename( $path ) ) {
			$path = dirname( $path );
		}

		$path = self::getProjectRoot( $path );

		return "{$path}wp-cli.yml";
	}
}