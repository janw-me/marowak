<?php

namespace Marowak\Helper;

/**
 * Class XML
 *
 * @package Marowak\Helper
 */
class XML {
	public static function getSimpleXml( $xml_file ) {
		if ( ! is_readable( $xml_file ) ) {
			throw new \Exception( 'Project doesn\'t have a vcs.xml file.' );
		}

		$simple = simplexml_load_file( $xml_file );
		if ( ! $simple ) {
			throw new \Exception( "Unknown error while loading file '{$xml_file}'" );
		}

		return $simple;
	}

	/**
	 * Get the version control directories registered in the idea.
	 *
	 * @param string $path      Path to the .idea folder.
	 * @param bool   $skip_root Skip the project root if it's registered.
	 *
	 * @return array The directories that are registered. 'final-folder' => 'path'
	 * @throws \Exception
	 */
	public static function getVersionControlDirectories( string $path, bool $skip_root = true ): array {
		$path       = Paths::getIdea( $path );
		$simple_xml = self::getSimpleXml( "$path/vcs.xml" );

		$directories = array();
		if ( empty( $simple_xml->component ) ) {
			return array(); // no Version control directories.
		}

		foreach ( $simple_xml->component->children() as $item ) {
			if ( empty( $item['directory'] ) ) {
				throw new \Exception( "vcs.xml is invalid and doesn't have a directory." );
			};
			$dir = str_replace( '$PROJECT_DIR$', '', (string) $item['directory'] );
			if ( ( empty( $dir ) || '/' === $dir ) && $skip_root ) {
				continue; // This is the root dir we don't register that.
			}
			$slug                 = basename( $dir );
			$directories[ $slug ] = rtrim( ltrim( $dir, '/' ), '/' ) . '/';
		}

		return $directories;
	}

	/**
	 * Get the scope directories registered in the idea.
	 *
	 * @param string $path Path to the .idea folder.
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function getScopeDirectories( string $path ) {
		$path       = Paths::getIdea( $path );
		$simple_xml = self::getSimpleXml( "$path/workspace.xml" );

		$scopes = $simple_xml->xpath( 'component[@name="NamedScopeManager"]' );
		if ( empty( $scopes ) ) {
			return array();
		}
		$scope_names = array();

		if ( empty( $scopes[0] ) ) {
			return array();
		}
		foreach ( $scopes[0]->children() as $scope ) {
			if ( empty( $scope['name'] ) ) {
				continue; // no name no chance.
			}
			$scope_names[ (string) $scope['name'] ] = (string) $scope['pattern'];
		}

		return $scope_names;
	}

	/**
	 * Get the Colored files registered in the idea.
	 *
	 * @param string $path Path to the .idea folder.
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function getFileColors( string $path ) {
		$path       = Paths::getIdea( $path );
		$simple_xml = self::getSimpleXml( "$path/workspace.xml" );

		$scopes = $simple_xml->xpath( 'component[@name="FileColors"]' );
		if ( empty( $scopes ) ) {
			return array();
		}
		$scope_names = array();

		if ( empty( $scopes[0] ) ) {
			return array();
		}
		foreach ( $scopes[0]->children() as $scope ) {
			if ( empty( $scope['scope'] ) ) {
				continue; // no scope no chance.
			}
			$scope_names[ (string) $scope['scope'] ] = (string) $scope['color'];
		}

		return $scope_names;
	}

}