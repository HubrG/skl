<?php

namespace App\Controller\Admin;

use App\Entity\PublicationComment;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PublicationCommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PublicationComment::class;
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
