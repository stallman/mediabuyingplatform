<?php


namespace App\Controller\Front\Acme;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Routing\Annotation\Route;

class AcmeController extends AbstractController
{
    /**
     * @Route("/.well-known/acme-challenge/{filename}", name="acme.auth")
     * @param string $filename
     * @return Response
     */
    public function acmeAuth(string $filename)
    {
        $file = new SplFileInfo($_ENV['ACME_PATH'].$filename, '', '');

        return new Response($file->getContents(), 200, ['Content-Type' => 'text/html']);
    }
}



