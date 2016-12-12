<?php

namespace TomGud\Command;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use TomGud\Model\HttpIgnore;
use TomGud\Model\Specification;
use TomGud\Service\SpecificationParser;

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
            ->addArgument('spec', InputArgument::REQUIRED, 'Specification file');
    }

    /**
     * @inheritdoc
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $specification = $this->parseConfigFile($input->getArgument('spec'));

        $clientA = new GuzzleClient(['base_uri' => $specification->getBaseUri()[0]]);
        $clientB = new GuzzleClient(['base_uri' => $specification->getBaseUri()[1]]);

        foreach ($specification->getCase() as $id => $case) {
            if ($case->getUri() === null) {
                $output->writeln('<info>Ignoring case ' . $id . ' with no path</info>');
                continue;
            }
            $output->write('[' . $id . '] Comparing ' . $case->getMethod() . ' ' . $case->getUri() . '');
            $options = [
                RequestOptions::QUERY => $case->getQuery(),
                RequestOptions::HEADERS => $case->getHeaders(),
                RequestOptions::BODY => $case->getBody(),
                RequestOptions::SYNCHRONOUS => true
            ];
            $responseA = $clientA->request($case->getMethod(), $case->getUri(), $options);
            $responseB = $clientB->request($case->getMethod(), $case->getUri(), $options);
            if (!$this->compareResponses($responseA, $responseB, $specification->getIgnore())) {
                $output->write(' <error>✗</error>');
            } else {
                $output->write(' <info>✓</info>');
            }
            $output->writeln('');
        }
    }

    /**
     * @param string $filename
     * @return Specification
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     * @throws \InvalidArgumentException
     */
    private function parseConfigFile(string $filename)
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('File not found : ' . $filename);
        }

        $contents = file_get_contents($filename);
        if (strpos($filename, 'json') === strlen($filename) - 4) {
            $decodedContent = json_decode($contents, true);
        } elseif(strpos($filename, 'yml') === strlen($filename) - 3) {
            $decodedContent = Yaml::parse($contents);
        } else {
            throw new \InvalidArgumentException('File format not supported, json and yml only');
        }
        $parser = new SpecificationParser($decodedContent);
        return $parser->parse();
    }

    /**
     * @param ResponseInterface $responseA
     * @param ResponseInterface $responseB
     * @param HttpIgnore $ignore
     * @return bool
     */
    private function compareResponses(ResponseInterface $responseA, ResponseInterface $responseB, $ignore)
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
        $headerDiff = array_diff_key($headerDiff, array_flip($ignore->getHeaders()));
        $responseCodeEquals = $responseA->getStatusCode() === $responseB->getStatusCode();

        return
            ($ignore->isHtml()|| $htmlEquals) &&
            ($ignore->isStatusCode() || $responseCodeEquals) &&
            count($headerDiff) === 0;
    }

}
