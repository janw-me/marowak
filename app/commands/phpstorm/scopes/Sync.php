<?php

namespace Marowak\Commands\Phpstorm\Scopes;

use Marowak\Helper\Paths;
use Marowak\Helper\XML;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ScopeCommand
 *
 * @package Marowak
 */
class Sync extends Command {

	protected $scope_colors = array( 'Blue', 'Green', 'Orange', 'Pink', 'Violet', 'Yellow' );

	protected static $defaultName = 'phpstorm:scopes:sync';

	protected function configure(): void {
		$this->addArgument( 'path', InputArgument::OPTIONAL, 'where is the project located? Optional' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {

		$path = $input->getArgument( 'path' );
		$path = Paths::getIdea( $path );

		if ( is_a( $path, '\Exception' ) ) {
			$output->write( "<error>{$path->getMessage()}</error>\n" );

			return Command::FAILURE;
		}

		$dirs   = XML::getVersionControlDirectories( $path );
		$scopes = XML::getScopeDirectories( $path );
		$colors = XML::getFileColors( $path );

		$this->mergeAll( $dirs, $scopes, $colors );

		var_dump( $colors );

		$this->updateXML( $path, $scopes, $colors );

		return Command::SUCCESS;
	}

	protected function mergeAll( $git, &$scopes, &$colors ) {
		$i = 0;
		foreach ( $git as $slug => $dir ) {
			if ( ! isset( $scopes[ $slug ] ) ) {
				$scopes[ $slug ] = "file:{$dir}/*";
			}
			if ( ! isset( $colors[ $slug ] ) ) {
				var_dump( $colors );
				$colors[ $slug ] = $this->scope_colors[ $i % count( $this->scope_colors ) ];
			}
			$i ++;
		}
	}

	protected function updateXML( string $path, array $scopes, array $colors ) {
		$simple_xml = XML::getSimpleXml( "$path/workspace.xml" );

		/** @var \SimpleXMLElement $scopes_xml */
		$scopes_xml = $simple_xml->xpath( 'component[@name="NamedScopeManager"]' );

		if ( empty( $scopes_xml[0] ) ) {
			$scopes_xml = $simple_xml->addChild( 'component' );
			$scopes_xml->addAttribute( 'name', 'NamedScopeManager' );
		} else {
			unset( $scopes_xml[0]->scope );
			$scopes_xml = $scopes_xml[0];
		}

		foreach ( $scopes as $slug => $scope ) {
			$scope_child = $scopes_xml->addChild( 'scope' );
			$scope_child->addAttribute( 'name', $slug );
			$scope_child->addAttribute( 'pattern', $scope );
		}

		/** @var \SimpleXMLElement $scopes_xml */
		$colors_xml = $simple_xml->xpath( 'component[@name="FileColors"]' );

		if ( empty( $colors_xml[0] ) ) {
			$colors_xml = $simple_xml->addChild( 'component' );
			$colors_xml->addAttribute( 'name', 'FileColors' );
		} else {
			unset( $colors_xml[0]->fileColor );
			$colors_xml = $colors_xml[0];
		}

		foreach ( $colors as $slug => $scope ) {
			$colors_child = $colors_xml[0]->addChild( 'fileColor' );
			$colors_child->addAttribute( 'scope', $slug );
			$colors_child->addAttribute( 'color', $scope );
		}

		$simple_xml->saveXML( "$path/workspace.xml" );
	}
}