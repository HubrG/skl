<?php

namespace App\Components;

use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('feed_user_nav_component')]
class FeedUserNavComponent
{
    use DefaultActionTrait;
    #[LiveProp]
    public int $userId;
    public function __construct()
    {
    }
}
