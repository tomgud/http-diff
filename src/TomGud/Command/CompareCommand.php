<?php

namespace TomGud\Command;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
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
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->parseConfigFile($input->getArgument('config'));

        if (!isset($config['base_uri']) || !is_array($config['base_uri']) || count($config['base_uri']) !== 2) {
            throw new \RuntimeException('Base URI\'s are not configured or there are not exactly two of them');
        }

        $clientA = new GuzzleClient(['base_uri' => $config['base_uri'][0]]);
        $clientB = new GuzzleClient(['base_uri' => $config['base_uri'][1]]);

        if (!isset($config['cases'])) {
            $output->writeln('<info>No cases were found. Stopping</info>');
        }

        foreach ($config['cases'] as $id => $case) {
            $uri = array_key_exists('path', $case) ? $case['path'] : null;
            $method = array_key_exists('method', $case) ? $case['method'] : 'GET';
            $query = array_key_exists('query', $case) ? $case['query'] : [];
            $headers = array_key_exists('headers', $case) ? $case['headers'] : [];
            $content = array_key_exists('content', $case) ? $case['content'] : null;

            if ($uri === null) {
                $output->writeln('<info>Ignoring case ' . $id . ' with no path</info>');
                continue;
            }
            $output->write('[' . $id . '] Comparing ' . $method . ' ' . $uri . '');
            $responseA = $clientA->request(
                $method,
                $uri,
                [
                    RequestOptions::QUERY => $query,
                    RequestOptions::HEADERS => $headers,
                    RequestOptions::BODY => $content,
                    RequestOptions::SYNCHRONOUS => true
                ]
            );
            $responseB = $clientB->request(
                $method,
                $uri,
                [
                    RequestOptions::QUERY => $query,
                    RequestOptions::HEADERS => $headers,
                    RequestOptions::BODY => $content,
                    RequestOptions::SYNCHRONOUS => true
                ]
            );
            if (!$this->compareResponses($responseA, $responseB)) {
                $output->write(' <error>✗</error>');
            } else {
                $output->write(' <info>✓</info>');
            }
            $output->writeln('');
        }
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

    /**
     * @param ResponseInterface $responseA
     * @param ResponseInterface $responseB
     * @return bool
     */
    private function compareResponses(ResponseInterface $responseA, ResponseInterface $responseB) : bool
    {
        $htmlEquals = $responseA->getBody()->getContents() === $responseB->getBody()->getContents();
        $headersA = $responseA->getHeaders();
        $headersB = $responseB->getHeaders();
        $headerDiff = [];
        foreach ($headersA as $keyA => $headerA) {
            if (array_key_exists($keyA, $headersB)) {
                foreach (array_diff($headerA, $headersB[$keyA]) as $diff) {
                    $headerDiff[$keyA]['<'] = $diff;
                }
            } else {
                // Missing header in B that exists in A
                $headerDiff[$keyA]['>'] = implode(';', $headerA);
            }
        }
        foreach ($headersB as $keyB => $headerB) {
            if (array_key_exists($keyB, $headersA)) {
                foreach (array_diff($headerB, $headersA[$keyB]) as $diff) {
                    $headerDiff[$keyB]['>'] = $diff;
                }
            } else {
                // Missing header in A that exists in B
                $headerDiff[$keyB]['>'] = implode(';', $headerB);
            }
        }
        $responseCodeEquals = $responseA->getStatusCode() === $responseB->getStatusCode();
        return $htmlEquals && $responseCodeEquals && count($headerDiff) === 0;
    }

}
