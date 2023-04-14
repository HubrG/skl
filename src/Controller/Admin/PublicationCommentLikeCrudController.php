<?php

namespace App\Controller\Admin;

use App\Entity\PublicationCommentLike;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PublicationCommentLikeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PublicationCommentLike::class;
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
