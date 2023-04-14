<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AvatarField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LanguageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }


    public function configureFields(string $pageName): iterable
    {
        $profilPictureField = TextField::new('profilPicture', 'PP')
            ->setVirtual(true)
            ->setTemplatePath('admin/fields/profil_picture.html.twig');
        $profilBackgroundField = TextField::new('profilBackground', 'PBG')
            ->setVirtual(true)
            ->hideOnIndex()
            ->setTemplatePath('admin/fields/profil_background.html.twig');
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('username', 'Compte')->setSortable(true)->formatValue(function ($value, $entity) {
                return "<strong>" . $entity->getUsername() . "</strong>";
            }),
            $profilPictureField,
            $profilBackgroundField,
            TextField::new('nickname', 'Surnom')->setSortable(true),
            EmailField::new('email', 'Email')->setSortable(true),
            BooleanField::new('isVerified', 'Vérifié')->setSortable(true),
            ArrayField::new('roles', 'Rôles')->hideOnForm(),
            TextField::new('city', 'Ville')->setSortable(true),
            CountryField::new('country', 'Pays')->setSortable(true),
            TextEditorField::new('about', 'À propos')->hideOnIndex(),
            DateTimeField::new('join_date', 'Membre depuis')->setSortable(true)->hideOnForm(),
            TextField::new('twitter', "Twitter")->hideOnIndex(),
            TextField::new('facebook', "Facebook")->hideOnIndex(),
            TextField::new('instagram', "Instagram")->hideOnIndex(),
            TextField::new('googleId', "GID")->hideOnForm(),

        ];
    }
    public function configureCrud(Crud $crud): Crud
    {

        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Utilisateurs')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détails du chapitre de publication')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le chapitre de publication')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un chapitre de publication')
            ->setEntityLabelInSingular('un nouveau chapitre')
            ->setEntityLabelInPlural('un chapitre')
            ->setDefaultSort(['id' => 'DESC']);
    }
}
