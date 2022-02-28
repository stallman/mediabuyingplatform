<?php


namespace App\Controller\Dashboard\Traits;


use App\Entity\News;

trait NewsTrait
{
    public function list(){}
    public function add() {}
    public function edit(News $news){}
}