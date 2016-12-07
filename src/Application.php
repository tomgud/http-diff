#!/usr/bin/env php
<?php
require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use TomGud\Command\CompareCommand;

$application = new Application('http-diff', '1.0.0');
$command = new CompareCommand();

$application->add($command);

$application->setDefaultCommand($command->getName(), true);
$application->run();
