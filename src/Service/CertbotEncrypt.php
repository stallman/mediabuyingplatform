<?php


namespace App\Service;


use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class CertbotEncrypt
{
    private LoggerInterface $logger;
    public Filesystem $filesystem;
    private static string $errMsg = '';
    private string $nginxConfDir;

    public function __construct(ParameterBagInterface $params, LoggerInterface $logger)
    {
        $this->params = $params;
        $this->logger = $logger;
        $this->filesystem = new Filesystem();
        $this->nginxConfDir = $this->params->get('kernel.root_dir') . '/nginx-configs/';
    }

    public function letsEncrypt(string $domain, bool $isTest = false): string
    {
        $isParsed = $this->parseNginxCfg($domain);

        if ($isParsed) {
            $this->execCmd($domain, $isTest);
        }

        return self::$errMsg;
    }

    public function removeNginxCfg(string $domain): void
    {
        if (!is_dir($this->nginxConfDir)) {
            return;
        }

        if (is_file($this->nginxConfDir . $domain)) {
            unlink($this->nginxConfDir . $domain);
        }
    }

    private function execCmd(string $domain, bool $isTest): void
    {

        if ($isTest) {
            $cmd = "echo '{$_ENV['SUDO_PASSWORD']}' | sudo -S certbot certonly --nginx --non-interactive -d $domain -d www.$domain --dry-run"; // test certififcate
            exec($cmd);
            /**
             * next to do for test:
             * check log /var/log/letsencrypt/letsencrypt.log
             * for message: DEBUG:certbot.reporter:Reporting to user: The dry run was successful.
             * */
        } else {
            $cmd = "echo '{$_ENV['SUDO_PASSWORD']}' | sudo -S certbot --nginx --non-interactive -d $domain -d www.$domain";
            exec($cmd);

            if (!str_contains(file_get_contents($this->nginxConfDir . $domain), 'ssl_certificate')) {
                $this->writeLog("Nginx config file for domain $domain does not contain ssl certificate record managed by Certbot!");
            }
        }
    }

    private function parseNginxCfg(string $domain): bool
    {
        try {
            $vars = [
                '{{domain}}' => $domain,
                '{{root_path}}' => $_ENV['ROOT_PATH'],
            ];

            $file = $this->params->get('kernel.root_dir') . '/' . $_ENV['NGINX_CERTBOT_CONFIG_EXAMPLE'];
            $template = file_get_contents($file);

            if (!is_dir($this->nginxConfDir)) {
                throw new \RuntimeException('Need make dir ' . $this->nginxConfDir);
            }

            $this->filesystem->dumpFile($this->nginxConfDir . $domain, strtr($template, $vars));

            if (!is_file($this->nginxConfDir . $domain)) {
                throw new \RuntimeException('File is not created ' . $this->nginxConfDir . $domain);
            }

            return true;
        } catch (\RuntimeException $e) {
            $this->writeLog("Cant create nginx config for domain $domain, with error message: " . $e->getMessage());
            return false;
        }
    }

    private function renew(): void
    {
        /**
         * check certbot renew timer
         * sudo systemctl status certbot.timer
         *
         * Most Certbot installations come with automatic renewals preconfigured.
         * This is done by means of a scheduled task which runs certbot renew periodically.
         * @doc https://certbot.eff.org/docs/using.html#automated-renewals
         *
         * config file for certbot renew
         * @doc https://certbot.eff.org/docs/using.html#configuration-file
         */
    }

    private function writeLog(string $message): void
    {
        $msg = 'Certififactes Error! ' . $message;
        $this->logger->error($msg);
        self::$errMsg = $msg;
    }
}
