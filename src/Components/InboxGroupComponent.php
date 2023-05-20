<?php

namespace App\Components;

use App\Entity\Inbox;
use App\Entity\InboxGroup;
use App\Entity\InboxGroupMember;
use Doctrine\ORM\Query\Expr\Join;
use App\Repository\InboxRepository;
use PhpParser\Node\Expr\Cast\Bool_;
use App\Repository\InboxGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use App\Repository\InboxGroupMemberRepository;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('inbox_group_component')]
class InboxGroupComponent
{
    use DefaultActionTrait;


    #[LiveProp]
    public ?int $id = null;

    #[LiveProp]
    public ?int $group = null;

    #[LiveProp]
    public ?int $big = null;

    public function __construct(private InboxRepository $inboxRepo, private EntityManagerInterface $em, private InboxGroupRepository $igRepo, private InboxGroupMemberRepository $igmRepo)
    {
    }

    public function getConversations(): array
    {
        // $conv =  $this->igmRepo->findBy(["user" => $this->id]);
        return $this->igmRepo->createQueryBuilder('igm')
            ->select('igm')
            ->innerJoin('App\Entity\Inbox', 'inbox', 'WITH', 'igm.grouped = inbox.grouped')
            ->where('igm.user = :userId')
            ->orderBy('inbox.CreatedAt', 'DESC')
            ->setParameter('userId', $this->id)
            ->getQuery()
            ->getResult();
    }
    public function getGroup(): int
    {
        return $this->big;
    }
}
