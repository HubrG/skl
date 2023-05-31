<?php

namespace App\Components;

use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('feed_component')]
class FeedComponent
{
    use DefaultActionTrait;

    public function __construct()
    {
    }
}
