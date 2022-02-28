<?php


namespace App\Traits\Dashboard;

use Symfony\Component\Yaml\Yaml;

trait FlashMessagesTrait
{
    public function getFlashMessage(string $key, ?array $options = [])
    {
        $flashMessages = Yaml::parseFile($this->getKernalRootPath() . "/config/flash-messages.yaml");
        $message = $flashMessages[$key];

        return sprintf($message, ...$options);
    }
    
    private function getKernalRootPath()
    {
        return str_replace("public", "", $_SERVER['DOCUMENT_ROOT']);
    }

    private function strReplaceOnce($search, $replace, $text) 
    { 
        $pos = strpos($text, $search); 
        return $pos!==false ? substr_replace($text, $replace, $pos, strlen($search)) : $text; 
    }
}