<?php
/**
 * Created by PhpStorm.
 * User: rmatk
 * Date: 17/02/2019
 * Time: 21:12
 */

namespace Acme;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Guzzlehttp\ClientInterface;
use ZipArchive;

class NewCommand extends Command {
	private $client;

	/**
	 * NewCommand constructor.
	 *
	 * @param ClientInterface $client
	 */
	public function __construct(ClientInterface $client) {
		$this->client = $client;
		parent::__construct();
	}


	public function configure() {
		$this->setName("new")
			->setDescription("Create new Laravel app")
			->addArgument("name", InputArgument::REQUIRED, "your name");
		// ->addOption("greeting", NULL, InputOption::VALUE_OPTIONAL, "Hello", "Hello");
	}


	public function execute(InputInterface $input, OutputInterface $output) {
		// $message = sprintf("%s, %s", $input->getOption("greeting"), $input->getArgument("name"));
		// $output->writeln("<info>{$message}</info>");

		// Search if folder isn't already exist
		$directory = getcwd()."/".$input->getArgument("name");
		$this->asserApplicationDoesNotExists($directory, $output);

		$output->writeln("<info>Crafting application</info>");

		// download laravel
		$this->download($zipFile = $this->makeFileName())
			// extract zip
			->extract($zipFile, $directory)
			->cleanUp($zipFile);

		// alert user
		$output->writeln("<comment>Application ready</comment>");
	}

	private function asserApplicationDoesNotExists($directory, OutputInterface $output) {
		if(is_dir($directory)) {
			$output->writeln("\n");
			$output->writeln("<error>app already exists</error>");
			$output->writeln("\n");
			exit(1);
		}
	}

	private function makeFileName() {
		return getcwd() ."/laravel_" . md5(time().uniqid()) . ".zip";
	}

	private function download($zipFile) {
		$response =	$this->client->request("GET","http://cabinet.laravel.com/latest.zip")->getBody();
		file_put_contents($zipFile, $response);
		return $this;
	}

	public function extract($zipFile ,$directory) {
		$archive = new ZipArchive();
		$archive->open($zipFile);
		$archive->extractTo($directory);
		$archive->close();
		return $this;
	}

	private function cleanUp($zipFile){
		@chmod($zipFile, 0777);
		@unlink($zipFile);

		return $this;
	}
}