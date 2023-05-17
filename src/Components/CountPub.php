<?php

namespace App\Components;

use App\Repository\PublicationRepository;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('count_pub')]
class CountPub
{
    use DefaultActionTrait;


    public function __construct(private PublicationRepository $pRepo)
    {
    }

    public function getPub(): int
    {

        return $this->pRepo->count([]);
    }
}
