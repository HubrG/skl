<?php
// src/Components/Alert.php
namespace App\Components;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('user')]
class UserInfo
{
    public int $id;

    public string $type;

    public function __construct(private UserRepository $userRepository)
    {
    }

    public function getUser(): User
    {
        return $this->userRepository->find($this->id);
    }
}
