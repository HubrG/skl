<?php

namespace App\Controller\Admin;

use App\Entity\PublicationPopularity;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PublicationPopularityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PublicationPopularity::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
