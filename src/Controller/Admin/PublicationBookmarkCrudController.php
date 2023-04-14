<?php

namespace App\Controller\Admin;

use App\Entity\PublicationBookmark;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PublicationBookmarkCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PublicationBookmark::class;
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
