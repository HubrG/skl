<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class TwigEntityType extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_entity_type', [$this, 'getEntityType']),
        ];
    }

    public function getEntityType($entity): string
    {
        $class = get_parent_class($entity);
        if (!$class) {
            // Ce n'est pas une entité proxy, obtenons simplement la classe normalement
            $class = get_class($entity);
        }
        $parts = explode('\\', $class);
        return end($parts);
    }
}
