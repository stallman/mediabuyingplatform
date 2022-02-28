<?php

namespace App\Traits;

use Mobile_Detect;
use UAParser\Parser;

trait DeviceTrait
{
    /**
     * @return string
     */
    public function getUserDevice()
    {
        $detect = new Mobile_Detect;

        $isMobile = $detect->isMobile();
        $isTablet = $detect->isTablet();

        return ($isMobile ? ($isTablet ? 'tablet' : 'mobile') : 'desktop');
    }

    public function parseUserAgent()
    {
        $parser = Parser::create();
        $userAgent = $this->request->server->get('HTTP_USER_AGENT') ? $this->request->server->get('HTTP_USER_AGENT') : '';
        return $parser->parse($userAgent);
    }
}