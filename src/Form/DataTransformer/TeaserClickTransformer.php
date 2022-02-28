<?php
namespace App\Form\DataTransformer;

use App\Entity\TeasersClick;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class TeaserClickTransformer implements DataTransformerInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param  TeasersClick|null $teaserClick
     * @return string
     */
    public function transform($teaserClick)
    {
        if (is_null($teaserClick)) {
            return null;
        }

        return $teaserClick->getId();
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string $teaserClickId
     * @return object
     * @throws TransformationFailedException if object (TeasersClick) is not found.
     */
    public function reverseTransform($teaserClickId)
    {
        if (!$teaserClickId) {
            return;
        }

        $teaserCLick = $this->entityManager
            ->getRepository(TeasersClick::class)
            ->find($teaserClickId)
        ;

        if (null === $teaserCLick) {
            throw new TransformationFailedException(sprintf(
                'An teaser click with number "%s" does not exist!',
                $teaserClickId
            ));
        }

        return $teaserCLick;
    }
}