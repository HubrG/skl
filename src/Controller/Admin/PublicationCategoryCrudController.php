<?php

namespace App\Controller\Admin;

use App\Entity\PublicationCategory;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PublicationCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PublicationCategory::class;
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
