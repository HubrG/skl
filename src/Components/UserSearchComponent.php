<?php

namespace App\Components;

use App\Repository\UserRepository;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('user_search')]
class UserSearchComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(private UserRepository $uRepo)
    {
    }

    public function getUsers(): array
    {

        return $this->uRepo->findByQuery($this->query);
    }
}
