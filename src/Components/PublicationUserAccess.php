<?php

namespace App\Components;

use App\Repository\UserRepository;
use App\Repository\ForumTopicRepository;
use App\Repository\PublicationRepository;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsLiveComponent('publication_user_access')]
class PublicationUserAccess extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';
    #[LiveProp()]
    public int $id = 0;
    public function __construct(
        private UserRepository $uRepo,

    ) {
    }

    public function getUsers()
    {
        return $this->uRepo->userAccessFindByQuery($this->query, $this->id, $this->getUser()->getId());
    }
}
