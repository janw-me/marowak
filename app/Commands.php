<?php

namespace Marowak;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
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


	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$this->input  = $input;
		$this->output = $output;

		return Command::SUCCESS; // just for type hinting.
	}
}