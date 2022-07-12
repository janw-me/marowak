<?php

namespace Marowak\Commands\Phpstorm;

use Marowak\Commands;
use Marowak\Helper\XML;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class WordPress
 *
 * @package Marowak\Commands\Phpstorm
 */
class VersionControl extends Commands {

	protected static $defaultName = 'phpstorm:version-control';

	protected function configure(): void {
		parent::configure();
		$this->addOption( 'force', 'f',InputOption::VALUE_NONE, 'Force to update, even if already set.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		parent::execute( $input, $output );

		$this->updateXML( $this->getIdeaRoot() );

		return Command::SUCCESS;
	}

	protected function updateXML( string $path ): void {
		$simple_xml = XML::getSimpleXml( "$path/workspace.xml" );

		/** @var \SimpleXMLElement $scopes_xml */
		$component = $simple_xml->xpath( 'component[@name="ChangesViewManager"]' );
		if ( empty( $component[0] ) ) {
			$component = $simple_xml->addChild( 'component' );
			$component->addAttribute( 'name', 'ChangesViewManager' );
		} else {
			unset( $component[0]->option );
			$component = $component[0];
		}

		// cleanup WP_Path
		$option_1 = $component->addChild('option');
		$option_1->addAttribute( 'name', 'groupingKeys' );

		$option_2 = $option_1->addChild('option');
		$option_2->addAttribute( 'value', 'directory' );

		$simple_xml->saveXML( "$path/workspace.xml" );
	}

}
