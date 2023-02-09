<?php


namespace App\Twig;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TwigExists extends AbstractExtension
{
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }
    public function getFilters()
    {
        return [new TwigFilter("exists", [$this, "existsFilter"])];
    }
    public function existsFilter($file): string
    {
        $projectDir = $this->parameterBag->get('kernel.project_dir');
        return file_exists($projectDir . '/public' . $file);
    }
}
