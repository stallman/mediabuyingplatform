<?php

namespace App\Twig;

use App\Traits\DeviceTrait;
use Twig\TwigFunction;

class FrontExtension extends AppExtension
{
    use DeviceTrait;

    public function getFunctions()
    {
        return [
            new TwigFunction('get_device', [$this, 'getDevice']),
            new TwigFunction('get_env', [$this, 'getEnv']),
            new TwigFunction('teasers_click_counter_link', [$this, 'teasersClickCounterLink']),
            new TwigFunction('news_click_counter_link', [$this, 'newsClickCounterLink']),
            new TwigFunction('image_uploader_info', [$this, 'imageUploaderInfo']),
            new TwigFunction('is_test_remote_addr_ip', [$this, 'isTestRemoteAddrIp']),
        ];
    }

    public function getDevice()
    {
        return $this->getUserDevice();
    }
    
    public function getEnv($key)
    {
        return $_ENV[$key];
    }

    public function teasersClickCounterLink(array $teaser, ?\App\Entity\News $news, array $params)
    {
        $url = "/counting/" . $teaser['id'];
        if ($news instanceof \App\Entity\News) {
            $url = $url . "/" . $news->getId();
        }
        return $url . "?" . http_build_query($params);
    }

    public function newsClickCounterLink(array $news, array $params)
    {
        return "/counting_news/" . $news['id'] . "?" . http_build_query($params);
    }

    public function imageUploaderInfo()
    {
        $help = "Максимально допустимый размер файла: " . $_ENV['IMAGE_UPLOADER_MAX_SIZE'] . ". Поддерживаемые форматы: " . $_ENV['IMAGE_UPLOADER_EXT'] . ".";
        if ($_ENV['IMAGE_UPLOADER_MAX_PX_SIDE']) {
            $help .=  " Максимальный размер стороны изображения (px): " . $_ENV['IMAGE_UPLOADER_MAX_PX_SIDE'] . ".";
        }
        return $help;
    }

    public function isTestRemoteAddrIp() {
        if (isset($_ENV['TEST_REMOTE_ADDR_IP'])) {
            return $_SERVER['REMOTE_ADDR'] == $_ENV['TEST_REMOTE_ADDR_IP'];
        }
        return false;
    }
}