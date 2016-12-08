<?php

namespace TomGud\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class CompareCommand
 * @package TomGud\Command
 */
class CompareCommand extends Command
{
    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this->setName('http:diff')
            ->setDescription('http-diff compares two http responses and reports the difference')
            ->addArgument('config', InputArgument::REQUIRED, 'Configuration file');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->parseConfigFile($input->getArgument('config'));
        echo json_encode($config);
    }

    /**
     * @param string $filename
     * @return array
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     * @throws \InvalidArgumentException
     */
    private function parseConfigFile(string $filename) : array
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('File not found : ' . $filename);
        }

        $contents = file_get_contents($filename);
        if (strpos($filename, 'json') === strlen($filename) - 4) {
            return json_decode($contents, true);
        } elseif(strpos($filename, 'yml') === strlen($filename) - 3) {
            return Yaml::parse($contents);
        } else {
            throw new \InvalidArgumentException('File format not supported, json and yml only');
        }
    }

}
