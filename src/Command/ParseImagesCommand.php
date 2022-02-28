<?php
namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ParseImagesCommand extends Command
{
    const IMAGES_COUNT = 100;

    protected function configure()
    {
        $this
            ->setName('app:parse:images')
            ->setDescription('Спарсить изображения')
            ->setHelp('Эта команда скачивает изображения с i.picsum.photos в папку IMAGES_PATH, указанную в .env')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo "Начинаем парсинг...\n";
        $imagesCount = self::IMAGES_COUNT;
        for ($i=0; $i < $imagesCount; $i++) {

            $url = "https://i.picsum.photos/id/" . $i . "/" . $this->getRandomResolution() . ".jpg";
            $target = $_ENV['SOURCE_IMAGES_PATH'] . "/" . md5($this->getMillisecondsTime()) . ".jpg";
            echo $url . "\n";

            if ($this->download($url, $target)) {
                echo "скачано " . ($i + 1) . " изображений из " . $imagesCount . "\n";
            } else {
                echo "при скачивании изображения " . $url . " произошла ошибка\n";
            }

            if(exif_imagetype($target) != IMAGETYPE_JPEG){
                echo "Не является файлом jpg и будет удалено\n";
                unlink($target);
                $imagesCount++;
            }

            sleep(0.3);
        }
        echo "Парсинг изображений завершен\n";

        return 0;
    }

    protected function getMillisecondsTime()
    {
        return round(microtime(true) * 1000);
    }

    protected function getRandomResolution() {
        if (rand(0,1)) {
            return "800/600";
        }
        return "600/800";
    }

    protected function download($url, $target) {
        if(!$hfile = fopen($target, "w"))return false;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.95 Safari/537.11');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FILE, $hfile);

        if(!curl_exec($ch)){
            curl_close($ch);
            fclose($hfile);
            unlink($target);
            return false;
        }

        fflush($hfile);
        fclose($hfile);
        curl_close($ch);
        return true;
    }
}