<?php

namespace App\Controller\Admin;

use App\Entity\PublicationChapter;
use phpDocumentor\Reflection\Types\Integer;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PublicationChapterCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PublicationChapter::class;
    }


    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_DETAIL === $pageName) {
            // Ajoutez ou modifiez les champs à afficher sur la page de détail
            $fields[] = DateTimeField::new('createdAt', 'Créé le');
        }
        return [
            // FormField::addTab('First Tab'),
            IdField::new('id')->hideOnForm(),
            TextField::new('title', "Titre")->formatValue(function ($value, $entity) {
                return "<strong>" . htmlspecialchars_decode($value) . "</strong>";
            }),
            IntegerField::new('status', "Publié")->formatValue(function ($value, $entity) {
                if ($value == 2) {
                    return "<span class='badge badge-success'>Oui</span>";
                } else {
                    return "<span class='badge badge-danger'>Non</span>";
                }
            }),
            // FormField::addTab('First Tabf'),
            TextEditorField::new('content', "Contenu")->formatValue(function ($value, $entity) {
                return htmlspecialchars_decode($value);
            }),
            NumberField::new('pop', 'Pop')->hideOnForm()->setSortable(true),
            AssociationField::new('publicationChapterViews', 'Vues')->hideOnForm()->setSortable(true),
            AssociationField::new('publicationChapterLikes', 'Likes')->hideOnForm()->setSortable(true),
            // AssociationField::new('publicationChapter', 'Versioning')->hideOnForm()->setSortable(true),
            AssociationField::new('publication', 'Publication référente')->formatValue(function ($value, $entity) {
                return $entity->getPublication()->getTitle() . "<br><small><small>(" . $entity->getPublication()->getUser()->getUsername() . ")</small></small>";
            }),
            IntegerField::new('order_display', 'Ordre')->setSortable(true),
            TextField::new('slug', "Slug")->hideOnIndex(),
            DateTimeField::new('created', 'Créé le'),
            DateTimeField::new('updated', 'Modifié le'),




        ];
    }
    public function configureCrud(Crud $crud): Crud
    {

        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des chapitres')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détails du chapitre de publication')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le chapitre de publication')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un chapitre de publication')
            ->setEntityLabelInSingular('un nouveau chapitre')
            ->setEntityLabelInPlural('un chapitre')
            ->setSearchFields(['id', 'title', 'content', 'status', 'publication.title', 'publication.user.username', 'created', 'updated'])
            ->setDefaultSort(['id' => 'DESC']);
    }
}
