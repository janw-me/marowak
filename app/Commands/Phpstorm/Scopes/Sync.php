<?php

namespace Marowak\Commands\Phpstorm\Scopes;

use Marowak\Commands;
use Marowak\Helper\XML;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ScopeCommand
 *
 * @package Marowak
 */
class Sync extends Commands {

	protected $scope_colors = array( 'Blue', 'Green', 'Orange', 'Pink', 'Violet', 'Yellow' );

	protected static $defaultName = 'phpstorm:scopes:sync';

	protected function execute( InputInterface $input, OutputInterface $output ): int {

		parent::execute( $input, $output );

		$dirs   = XML::getVersionControlDirectories( $this->getIdeaRoot() );
		$scopes = XML::getScopeDirectories( $this->getIdeaRoot() );
		$colors = XML::getFileColors( $this->getIdeaRoot() );

		$this->mergeAll( $dirs, $scopes, $colors );

		if ( $output->isVerbose() ) {
			$maxlen = max( array_map( 'strlen', $dirs ) );
			foreach ( $colors as $slug => $color ) {
				$path = str_pad( $dirs[ $slug ], $maxlen + 2 );
				$output->writeln( "<info>{$path}</info> => <comment>{$color}</comment>" );
			}
		}

		$this->updateXML( $this->getIdeaRoot(), $scopes, $colors );

		return Command::SUCCESS;
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

	protected function updateXML( string $path, array $scopes, array $colors ): void {
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