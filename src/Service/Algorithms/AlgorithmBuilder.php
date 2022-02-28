<?php


namespace App\Service\Algorithms;


class AlgorithmBuilder
{
    /**
     * @param int $algorithmId
     * @return IAlgorithm
     */
    public function getInstance(int $algorithmId): IAlgorithm
    {
        $instance = null;

        switch ($algorithmId) {
            case 2:
                $instance = new SectionAlgorithm;
                break;
            case 3:
                $instance = new ScreenAlgorithm;
                break;
            case 4:
                $instance = new HiddenBlocksAlgorithm;
                break;
            case 1:
            default:
                $instance = new RandomAlgorithm;
                break;
        }

        return $instance;
    }
}