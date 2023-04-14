<?php

namespace App\Controller\Admin;

use App\Entity\PublicationRating;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PublicationRatingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PublicationRating::class;
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
