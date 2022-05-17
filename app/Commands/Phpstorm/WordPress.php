<?php

namespace Marowak\Commands\Phpstorm;

use Marowak\Commands;
use Marowak\Helper\Paths;
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
class WordPress extends Commands {

	protected static $defaultName = 'phpstorm:wordpress';

	protected function configure(): void {
		parent::configure();
		$this->addOption( 'force', 'f',InputOption::VALUE_NONE, 'Force to update, even if already set.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		parent::execute( $input, $output );

		$wp_dir = XML::getWordPressDirectory( $this->getIdeaRoot() );

		if ( ! empty( $wp_dir ) && ! $input->getOption('force') ) {
			if ($output->isVerbose()) {
				$output->writeln('WordPress path already set. Use <info>-f</info> to force setting a new WP path.');
			}
			return Command::SUCCESS;
		}

		$wp_dir = Paths::getWpDir();
		if ($output->isVerbose()) {
			$output->writeln("Setting WordPress directory to <info>{$wp_dir}</info>");
		}
		$this->updateXML($this->getIdeaRoot(), $wp_dir );

		return Command::SUCCESS;
	}

	protected function updateXML( string $path, $wp_path ): void {
		$simple_xml = XML::getSimpleXml( "$path/workspace.xml" );

		/** @var \SimpleXMLElement $scopes_xml */
		$wp_component = $simple_xml->xpath( 'component[@name="WordPressConfiguration"]' );
		if ( empty( $wp_component[0] ) ) {
			$wp_component = $simple_xml->addChild( 'component' );
			$wp_component->addAttribute( 'name', 'NamedScopeManager' );
		} else {
			unset( $wp_component[0]->wordpressPath );
			$wp_component = $wp_component[0];
		}

		// cleanup WP_Path
		$wp_path = rtrim( str_replace($this->getProjectRoot(),'$PROJECT_DIR$/', $wp_path), '/' );


		$wp_component->addChild('wordpressPath', $wp_path);

		$simple_xml->saveXML( "$path/workspace.xml" );
	}

}