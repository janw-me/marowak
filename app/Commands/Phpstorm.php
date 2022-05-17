<?php

namespace Marowak\Commands;

use Marowak\Commands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Phpstorm
 *
 * @package Marowak\Commands
 */
class Phpstorm extends Commands {

	protected static $defaultName = 'phpstorm';

	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return int
	 * @throws \Exception
	 */
	protected function execute( InputInterface $input, OutputInterface $output ): int {
		parent::execute( $input, $output );

		$return_code = $this->execute_subs( 'phpstorm:wordpress', array( '--force' => true ) );
		$return_code = $this->execute_subs( 'phpstorm:scopes:sync' );

		return Command::SUCCESS;
	}

	/**
	 * @param string $command_name
	 * @param array  $arguments
	 *
	 * @return int
	 * @throws \Exception
	 */
	protected function execute_subs( string $command_name, array $arguments = array() ) {
		if ( $this->output->isVerbose() ) {
			$this->output->writeln("<comment>Running command:</comment> <info>{$command_name}</info>");
		}

		$command   = $this->getApplication()->find( $command_name );
		$arguments = new ArrayInput( $arguments );

		return $command->run( $arguments, $this->output );
	}
}