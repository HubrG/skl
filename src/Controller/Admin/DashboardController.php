<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Publication;
use App\Entity\Notification;
use App\Entity\UserParameters;
use App\Entity\PublicationFollow;
use App\Entity\PublicationRating;
use App\Entity\PublicationChapter;
use App\Entity\PublicationComment;
use App\Entity\PublicationKeyword;
use App\Entity\PublicationBookmark;
use App\Entity\PublicationCategory;
use App\Entity\PublicationDownload;
use Symfony\UX\Chartjs\Model\Chart;
use App\Entity\ResetPasswordRequest;
use App\Entity\PublicationPopularity;
use App\Entity\PublicationChapterLike;
use App\Entity\PublicationChapterNote;
use App\Entity\PublicationChapterView;
use App\Entity\PublicationCommentLike;
use App\Entity\PublicationChapterVersioning;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;


class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private ChartBuilderInterface $chartBuilder,
        private AdminUrlGenerator $adminUrlGenerator,
    ) {
    }
    //
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        $adminUrlGenerator = $this->adminUrlGenerator->setController(PublicationCategoryCrudController::class)->generateUrl();
        return $this->redirect($adminUrlGenerator);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Scrilab');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        //
        yield MenuItem::subMenu('Catégories', 'fas fa-masks-theater')->setSubItems([
            MenuItem::linkToCrud('Voir les catégories', 'fas fa-eye', PublicationCategory::class),
            MenuItem::linkToCrud('Créer une catégorie', 'fas fa-add', PublicationCategory::class)->setAction(Crud::PAGE_NEW),
            // MenuItem::linkToCrud('Publications', 'fas fa-list', PublicationCrudController::class),
        ]);
        // 
        yield MenuItem::subMenu('Utilisateurs', 'fas fa-user')->setSubItems([
            MenuItem::linkToCrud('Utilisateurs', 'fas fa-user', User::class),
            MenuItem::linkToCrud('Paramètres utilisateur', 'fas fa-user-gear', UserParameters::class),
            MenuItem::linkToCrud('Réinitialisations de mot de passe', 'fas fa-lock', ResetPasswordRequest::class),
            MenuItem::linkToCrud('Notifications', 'fas fa-bell', Notification::class)
        ]);
        yield MenuItem::subMenu('Récits', 'fas fa-book')->setSubItems([

            MenuItem::linkToCrud('Récits', 'fas fa-book', Publication::class),
            MenuItem::linkToCrud('Chapitres', 'fas fa-file', PublicationChapter::class),
            MenuItem::linkToCrud('Suivis', 'fas fa-bell', PublicationFollow::class),
            MenuItem::linkToCrud('Commentaires', 'fas fa-comment', PublicationComment::class),
            MenuItem::linkToCrud('❤ Commentaires', 'fas fa-heart', PublicationCommentLike::class),
            MenuItem::linkToCrud('Bookmarks', 'fas fa-bookmark', PublicationBookmark::class),
            MenuItem::linkToCrud('Lecteurs', 'fas fa-eye', PublicationChapterView::class),
            MenuItem::linkToCrud('Chapitres « liked »', 'fas fa-thumbs-up', PublicationChapterLike::class),
            MenuItem::linkToCrud('Versionning', 'fas fa-code-compare', PublicationChapterVersioning::class),
            MenuItem::linkToCrud('Notes des utilisateurs', 'fas fa-marker', PublicationChapterNote::class),
            MenuItem::linkToCrud('Téléchargements', 'fas fa-download', PublicationDownload::class),
            MenuItem::linkToCrud('Mots-clés', 'fas fa-hashtag', PublicationKeyword::class),
            MenuItem::linkToCrud('Popularité', 'fas fa-fire', PublicationPopularity::class),
            MenuItem::linkToCrud('Rating', 'fas fa-star', PublicationRating::class),
        ]);

        // yield MenuItem::linkToCrud('Publication Categories', 'fas fa-list', PublicationCategoryCrudController::class);
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}
