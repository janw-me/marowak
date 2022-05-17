<?php

namespace Marowak;

use Marowak\Helper\Paths;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Commands
 *
 * @package Marowak
 */
abstract class Commands extends Command {

	/**
	 * @var InputInterface
	 */
	protected $input;

	/**
	 * @var OutputInterface
	 */
	protected $output;

	/**
	 * @var string
	 */
	private $projectRoot;

	/**
	 * @var string
	 */
	private $ideaRoot;

	protected function configure(): void {
		$this->addOption( 'path', null, InputOption::VALUE_OPTIONAL, 'Where is the project located?' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$this->input  = $input;
		$this->output = $output;

		return Command::SUCCESS; // just for type hinting.
	}

	protected function getProjectRoot() {
		if ( empty( $this->projectRoot ) ) {
			$this->projectRoot = Paths::getProjectRoot( $this->input->getOption( 'path' ) );
		}

		return $this->projectRoot;
	}

	protected function getIdeaRoot() {
		if ( empty( $this->ideaRoot ) ) {
			$this->ideaRoot = Paths::getIdea( $this->input->getOption( 'path' ) );
		}

		return $this->ideaRoot;
	}
}