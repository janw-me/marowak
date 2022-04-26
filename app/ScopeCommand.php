<?php

namespace Marowak;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ScopeCommand
 *
 * @package Marowak
 */
class ScopeCommand extends Command {

	protected $scope_colors
		= array(
			'Blue',
			'Green',
			'Orange',
			'Pink',
			'Violet',
			'Yellow',
		);

	protected static $defaultName = 'scope:update';

	protected function configure(): void {
		$this->addArgument( 'path', InputArgument::OPTIONAL, 'where is the project located? Optional' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {

		$path = $input->getArgument( 'path' );
		$path = $this->getIdeaPath( $path );

		if ( is_a( $path, '\Exception' ) ) {
			$output->write( "<error>{$path->getMessage()}</error>\n" );

			return Command::FAILURE;
		}

		$dirs   = $this->getVCdirs( $path );
		$scopes = $this->getScopes( $path );
		$colors = $this->getFileColors( $path );

//		$dirs = array( 'mauw'      => '/asdf/',
//		               'spider'    => '/asdf/spider',
//		               'tim'       => '/asdf/',
//		               'twan-huis' => '/asdf/',
//		               'npo'       => '/asdf/',
//		               'npo1'      => '/asdf/',
//		               'npo2'      => '/asdf/',
//		               'npo3'      => '/asdf/',
//		               'college'   => '/asdf/',
//		);

		$this->mergeAll( $dirs, $scopes, $colors );

		$this->updateXML( $path, $scopes, $colors );


		var_dump( $dirs, $scopes, $colors );


		return Command::SUCCESS;
	}

	/**
	 * @param false|null|string $path
	 *
	 * @return string
	 * @throws \Exception
	 */
	protected function getIdeaPath( $path ): string {
		if ( empty( $path ) ) {
			$path = getcwd(); // Default to current working directory
		}
		if ( ! is_dir( $path ) ) {
			throw new \Exception( "Invalid directory '{$path}'" );
		}
		$path = rtrim( $path, "/" ) . '/'; // force trailing slash.

		if ( ! is_dir( "$path.idea" ) ) {
			throw new \Exception( "Directory doesn't contain an .idea folder: {$path}" );
		}

		return "$path.idea";
	}

	/**
	 * @param string $path
	 * @param bool   $skip_root
	 *
	 * @return array
	 * @throws \Exception
	 */
	protected function getVCdirs( string $path, $skip_root = true ): array {
		$simple_xml = $this->getSimpleXml( "$path/vcs.xml" );

		$directories = array();
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

	protected function getScopes( $path ) {
		$simple_xml = $this->getSimpleXml( "$path/workspace.xml" );

		$scopes = $simple_xml->xpath( 'component[@name="NamedScopeManager"]' );
		if ( empty( $scopes ) ) {
			return array();
		}
		$scope_names = array();

//		var_dump( $scopes[0]->children() );

		foreach ( $scopes[0]->children() as $scope ) {
			if ( empty( $scope['name'] ) ) {
				continue; // no name no chance.
			}
			$scope_names[ (string) $scope['name'] ] = (string) $scope['pattern'];
		}

		return $scope_names;
	}

	protected function getFileColors( $path ) {
		$simple_xml = $this->getSimpleXml( "$path/workspace.xml" );

		$scopes = $simple_xml->xpath( 'component[@name="FileColors"]' );
		if ( empty( $scopes ) ) {
			return array();
		}
		$scope_names = array();

		foreach ( $scopes[0]->children() as $scope ) {
			if ( empty( $scope['scope'] ) ) {
				continue; // no scope no chance.
			}
			$scope_names[ (string) $scope['scope'] ] = (string) $scope['color'];
		}

		return $scope_names;
	}

	protected function getSimpleXml( $path ) {
		if ( ! is_readable( $path ) ) {
			throw new \Exception( "Project doesn't have a vcs.xml file." );
		}

		return simplexml_load_file( $path );
	}

	protected function mergeAll( $git, &$scopes, &$colors ) {
		$i = 0;
		foreach ( $git as $slug => $dir ) {
			if ( ! isset( $scopes[ $slug ] ) ) {
				$scopes[ $slug ] = "file:{$dir}/*";
			}
			$colors[ $slug ] = $this->scope_colors[ $i % count( $this->scope_colors ) ];
			$i ++;
		}
	}

	protected function updateXML( $path, $scopes, $colors ) {
		$simple_xml = $this->getSimpleXml( "$path/workspace.xml" );

		/** @var \SimpleXMLElement $scopes_xml */
		$scopes_xml = $simple_xml->xpath( 'component[@name="NamedScopeManager"]' );

		unset($scopes_xml[0]->scope);

		foreach ( $scopes as $slug => $scope ) {
			$scope_child = $scopes_xml[0]->addChild( 'scope' );
			$scope_child->addAttribute( 'name', $slug );
			$scope_child->addAttribute( 'pattern', $scope );
		}

		/** @var \SimpleXMLElement $scopes_xml */
		$colors_xml = $simple_xml->xpath( 'component[@name="FileColors"]' );

		unset($colors_xml[0]->fileColor);
		
		foreach ( $colors as $slug => $scope ) {
			$colors_child = $colors_xml[0]->addChild( 'fileColor' );
			$colors_child->addAttribute( 'scope', $slug );
			$colors_child->addAttribute( 'color', $scope );
		}


		$simple_xml->saveXML("$path/workspace.xml");
	}
}