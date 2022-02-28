<?php

namespace App\Command;

use Elphin\PHPCertificateToolbox\CertificateStorageInterface;
use Elphin\PHPCertificateToolbox\DiagnosticLogger;
use Elphin\PHPCertificateToolbox\FilesystemCertificateStorage;
use Elphin\PHPCertificateToolbox\LEClient;
use Elphin\PHPCertificateToolbox\LEOrder;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class LetsEncryptDomainCommand extends Command
{

    public Filesystem $filesystem;
    public CertificateStorageInterface $filesystemCertificateStorage;
    public ClientInterface $httpClient;
    public DiagnosticLogger $logger;
    public LoggerInterface $psrLogger;


    public function __construct(LoggerInterface $psrLogger)
    {
        $this->psrLogger = $psrLogger;
        $this->logger = new DiagnosticLogger;
        $this->filesystem = new Filesystem();
        $this->filesystemCertificateStorage = new FilesystemCertificateStorage();
        $this->httpClient = new Client([
            'curl' => [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ],
            'verify' => false
        ]);
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:letsencrypt:domain')
            ->setDescription('Сгенерировать сертификат от LetsEncrypt для необходимого домена')
            ->setHelp('Эта команда генерирует сертификат от LetsEncrypt для необходимого домена')
            ->addArgument('domain', InputArgument::REQUIRED, 'Домен');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $domain = $input->getArgument('domain');

        if(!preg_match("/^(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i", $domain)){
            $output->writeln('<comment>Домен должен быть в формате domain.com</comment>');
            return 0;
        }

        $client = new LEClient([$_ENV['ACME_EMAIL']], (bool) $_ENV['LETSENCRYPT_TEST_MODE'], $this->logger, $this->httpClient, $this->filesystemCertificateStorage);

        $order = $client->getOrCreateOrder($domain, [$domain]);
        if(!$order->allAuthorizationsValid()){
            $pending = $order->getPendingAuthorizations(LEOrder::CHALLENGE_TYPE_HTTP);
            if(!empty($pending)){
                foreach($pending as $challenge) {
                    $this->filesystem->dumpFile($_ENV['ACME_PATH'] . $challenge['filename'], $challenge['content']);
                    $order->verifyPendingOrderAuthorization($challenge['identifier'], LEOrder::CHALLENGE_TYPE_HTTP);
                }
            }
        }
        if($order->allAuthorizationsValid()){
            if(!$order->isFinalized()) $order->finalizeOrder();
            if($order->isFinalized()) $order->getCertificate();
            $this->generateDomainConf($domain);
        } else {
            throw new \Exception('Ошибка получения сертификата. Проверьте настройки DNS.');
        }
        $this->logger->dumpConsole();

        return 1;
    }

    /**
     * @param string $domainName
     */
    private function generateDomainConf(string $domainName)
    {
        $vars = [
            '{{domain}}' => $domainName,
            '{{root_path}}' => $_ENV['ROOT_PATH'],
            '{{cert_path}}' => $_ENV['CERT_PATH']
        ];
        $template = file_get_contents($_ENV['NGINX_CONFIG_EXAMPLE'], FILE_USE_INCLUDE_PATH);
        $this->filesystem->dumpFile("nginx-configs/{$domainName}", strtr($template, $vars));
    }
}