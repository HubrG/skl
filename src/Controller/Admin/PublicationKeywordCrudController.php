<?php

namespace App\Controller\Admin;

use App\Entity\PublicationKeyword;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PublicationKeywordCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PublicationKeyword::class;
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
