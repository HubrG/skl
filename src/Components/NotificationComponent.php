<?php

namespace App\Components;

use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('notification_component')]
class NotificationComponent
{
    use DefaultActionTrait;

    public function __construct()
    {
    }
}
