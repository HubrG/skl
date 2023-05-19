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

#[AsLiveComponent('inbox_group_member_component')]
class InboxGroupMemberComponent
{
    use DefaultActionTrait;


    public ?int $id = null;


    public function __construct(private InboxRepository $inboxRepo, private InboxGroupRepository $igRepo, private InboxGroupMemberRepository $igmRepo)
    {
    }

    public function getUsers(): array
    {
        return $this->igmRepo->findBy(["grouped" => $this->id]);
    }
}
