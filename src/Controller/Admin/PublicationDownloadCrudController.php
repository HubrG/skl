<?php

namespace App\Controller\Admin;

use App\Entity\PublicationDownload;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PublicationDownloadCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PublicationDownload::class;
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
