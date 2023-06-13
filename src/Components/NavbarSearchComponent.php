<?php

namespace App\Components;

use App\Repository\UserRepository;
use App\Repository\ChallengeRepository;
use App\Repository\ForumTopicRepository;
use App\Repository\PublicationRepository;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('navbar_search_component')]
class NavbarSearchComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(
        private UserRepository $uRepo,
        private PublicationRepository $pRepo,
        private ForumTopicRepository $ftRepo,
        private ChallengeRepository $cRepo
    ) {
    }

    public function getUsers()
    {
        return $this->uRepo->findByQuery($this->query);
    }
    public function getPublications()
    {
        return $this->pRepo->findByQuery($this->query);
    }
    public function getTopics()
    {
        return $this->ftRepo->findByQuery($this->query);
    }
    public function getChallenges()
    {
        return $this->cRepo->findByQuery($this->query);
    }
    public function getChallengePublications()
    {
        return $this->pRepo->findByQueryChallenge($this->query);
    }
}
