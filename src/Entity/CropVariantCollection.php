<?php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;


class CropVariantCollection
{

    protected $name;
    protected $cropVariants;

    public function __construct()
    {
        $this->cropVariants = new ArrayCollection();
    }

    public function getCropVariants()
    {
        return $this->cropVariants;
    }
}
