<?php

namespace App\Components;

use App\Entity\Inbox;
use App\Entity\InboxGroup;
use App\Entity\InboxGroupMember;
use App\Repository\InboxRepository;
use App\Repository\InboxGroupRepository;
use App\Repository\PublicationRepository;
use App\Repository\InboxGroupMemberRepository;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('inbox_component')]
class InboxComponent
{
    use DefaultActionTrait;

    #[LiveProp]
    public int $id;



    public function __construct(private InboxRepository $inboxRepo, private InboxGroupRepository $igRepo, private InboxGroupMemberRepository $igmRepo)
    {
    }

    public function getMessages(): array
    {
        return $this->inboxRepo->findBy(["grouped" => $this->id]);
    }
}
