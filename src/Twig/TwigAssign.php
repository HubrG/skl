<?php


namespace App\Twig;

use DateTime;
use Twig\TwigFilter;
use Twig\Environment;
use App\Repository\UserRepository;
use Twig\Extension\AbstractExtension;

class TwigAssign extends AbstractExtension
{
    private $twig;
    private $userRepository;

    public function __construct(Environment $twig, UserRepository $userRepository)
    {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }
    public function getFilters()
    {
        return [
            new TwigFilter('assign', [$this, 'assign'], ['is_safe' => ['html']]),
        ];
    }
    public function assign(string $content): string
    {
        $content = ' ' . $content;
        $pattern = '/(@\w+)/';
        $content = preg_replace_callback($pattern, function ($matches) {
            $username = substr($matches[0], 1);
            $user = $this->userRepository->findOneBy(['username' => $username]);
            if ($user) {
                $return = '<a href="/user/' . $username . '" data-turbo-frame="_top" class="assign">@' . $user->getNickname() . '</a>';
                // if ($user->getProfilPicture() != null) {
                //     $return = '<span class="inline-flex flex-row gap-x-1 items-center text-inherit">
                //     <img class="h-4 w-4 rounded-full" srcset="' . $user->getProfilPicture() . '" alt="' . $user->getNickname() . '">
                //     ' . $return . '
                //     </span>';
                // }
                return $return;
            }
            return $matches[0];
        }, $content);
        return trim($content);
    }
}
