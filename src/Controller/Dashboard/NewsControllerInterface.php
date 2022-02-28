<?php


namespace App\Controller\Dashboard;


use App\Entity\News;

interface NewsControllerInterface
{
    public function listAction();
    public function addAction();
    public function editAction(News $news);
    public function deleteAction(News $news);
}