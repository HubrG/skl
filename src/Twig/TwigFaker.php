<?php


namespace App\Twig;

use Faker\Factory;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

class TwigFaker extends AbstractExtension
{
    // private $faker;

    // public function __construct(Factory $faker)
    // {
    //     $this->faker = $faker;
    // }
    public function getFilters()
    {
        return [new TwigFilter("faker", [$this, "fakerFilter"])];
    }
    public function fakerFilter($string, $length): string
    {
        if ($length == null) {
            $length = "";
        }
        $faker = new Factory();
        $faker = $faker->create('fr_FR');
        if ($string == "text") {
            $return = $faker->realTextBetween(100, $length);
        } elseif ($string == "name") {
            $return = $faker->name($length);
        } elseif ($string == "title") {
            $return = $faker->sentence($length);
        }
        return $return;
    }
}
