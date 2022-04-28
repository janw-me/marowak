<?php

namespace Marowak\Commands\LocalWp\Setup;

use Marowak\Commands;
use Marowak\Helper\Paths;
use Marowak\Helper\URL;
use Marowak\Helper\WP_CLI_yml;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class Sync
 *
 * @package Marowak\Commands\LocalWp\Setup
 */
class WP_Cli extends Commands {

	protected $project_root;

	protected $yml = array();

	protected static $defaultName = 'localwp:setup:wpcli';

	protected function configure(): void {
		$this->addOption( 'path', null, InputOption::VALUE_OPTIONAL, 'Where is the project located?' );
		$this->addOption( 'live-ssh', null, InputOption::VALUE_OPTIONAL, 'The live ssh for wp-cli.yml' );
		$this->addOption( 'live-path', null, InputOption::VALUE_OPTIONAL, 'The live path for wp-cli.yml' );
		$this->addOption( 'live-url', null, InputOption::VALUE_OPTIONAL, 'The live url for wp-cli.yml' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {

		parent::execute($input, $output);

		$this->project_root = Paths::getProjectRoot( $input->getOption( 'path' ) );
		$live_ssh           = $this->optionAskIfMissing( 'live-ssh' );
		$live_path          = $this->optionAskIfMissing( 'live-path' );
		$live_url           = $this->optionAskIfMissing( 'live-url' );

		$local_path = str_replace( $this->project_root, '', Paths::getWpDir( $this->project_root ) );
		$local_url  = URL::getLocalUrl( $this->project_root );

		// create wp-cli.yml
		$yml = new WP_CLI_yml( $this->project_root );
		$yml->setVar( 'ssh', $live_ssh, '@live' );
		$yml->setVar( 'path', $live_path, '@live' );
		$yml->setVar( 'url', $live_url, '@live' );

		$yml->setVar( 'path', $local_path );
		$yml->setVar( 'url', $local_url );

		$yml->saveData();

		// TODO debug info?
		// TODO sucess message.

		return Command::SUCCESS;
	}

	/**
	 * Get an option value and ask for it if missing.
	 *
	 * @param string $option_name
	 *
	 * @return string
	 */
	protected function optionAskIfMissing( string $option_name ) {
		$option = $this->input->getOption( $option_name );
		if ( ! empty( $option ) ) {
			return $option;
		}

		/** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
		$helper   = $this->getHelper( 'question' );
		$question = new Question( "Please enter the {$option_name}:\n" );

		$answer = $helper->ask( $this->input, $this->output, $question );
		if ( empty( $answer ) ) {
			$answer = $this->optionAskIfMissing($option_name);
		}
		return $answer;
	}
}