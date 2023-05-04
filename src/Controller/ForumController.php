<?php

namespace App\Controller;

use App\Form\ForumTopicType;
use App\Form\ForumMessageType;
use App\Repository\ForumTopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ForumCategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ForumController extends AbstractController
{
    #[Route('/forum', name: 'app_forum')]
    public function index(ForumCategoryRepository $fcRepo): Response
    {
        $categories = $fcRepo->findAll();

        return $this->render('forum/index.html.twig', [
            'category' => $categories,
        ]);
    }
    #[Route('/forum/{slug}', name: 'app_forum_topic')]
    public function topic(ForumTopicRepository $ftRepo, ForumCategoryRepository $fcRepo, $slug = null): Response
    {
        $category = $fcRepo->findOneBy(['slug' => $slug]);
        if (!$category) {
            // redirection vers la route app_forum
            return $this->redirectToRoute('app_forum');
        }
        $topics = $ftRepo->findBy(['category' => $category]);


        return $this->render('forum/topics.html.twig', [
            'category' => $category,
            'topics' => $topics,
        ]);
    }
    #[Route('/forum/{slug}/create', name: 'app_forum_topic_create')]
    public function createTopic(Request $request, ForumTopicRepository $ftRepo, SluggerInterface $slugger, EntityManagerInterface $em, ForumCategoryRepository $fcRepo, $slug = null): Response
    {
        $category = $fcRepo->findOneBy(['slug' => $slug]);
        if ($this->getUser() == null) {
            return $this->redirectToRoute('app_forum_topic', ['slug' => $slug]);
        }
        if (!$category) {
            // redirection vers la route app_forum
            return $this->redirectToRoute('app_forum');
        }
        $topics = $ftRepo->findBy(['category' => $category]);
        // ! form
        $form = $this->createForm(ForumTopicType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Créez une nouvelle instance de PublicationTopic
            $topic = $form->getData();

            // Définissez les propriétés supplémentaires
            $topic->setCategory($category)
                ->setUser($this->getUser())
                ->setPermanent(0)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setSlug(strtolower($slugger->slug($topic->getTitle())));
            // Persistez et enregistrez l'entité
            $em->persist($topic);
            $em->flush();
            // On redirige vers le topic
            return $this->redirectToRoute('app_forum_topic_read', [
                'slug' => $category->getSlug(),
                'id' => $topic->getId(),
                'slugTopic' => $topic->getSlug(),
            ]);
        }
        return $this->render('forum/create_topic.html.twig', [
            'category' => $category,
            'topics' => $topics,
            'form' => $form,
        ]);
    }
    #[Route('/forum/{slug}/{id}/{slugTopic}', name: 'app_forum_topic_read')]
    public function readTopic(Request $request, ForumTopicRepository $ftRepo, SluggerInterface $slugger, EntityManagerInterface $em, ForumCategoryRepository $fcRepo, $id = null, $slugTopic = null, $slug = null): Response
    {
        $category = $fcRepo->findOneBy(['slug' => $slug]);

        if (!$category) {
            // redirection vers la route app_forum
            return $this->redirectToRoute('app_forum');
        }
        $topic = $ftRepo->find($id);
        if (!$topic) {
            // redirection vers la route app_forum
            return $this->redirectToRoute('app_forum_topic', ['slug' => $slug]);
        }

        // ! form
        $form = $this->createForm(ForumMessageType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Créez une nouvelle instance de PublicationTopic
            $message = $form->getData();
            // Définissez les propriétés supplémentaires
            $message->setTopic($topic)
                ->setUser($this->getUser())
                ->setCreatedAt(new \DateTimeImmutable());
            // Persistez et enregistrez l'entité
            $em->persist($message);
            $em->flush();
        }


        return $this->render('forum/read_topic.html.twig', [
            'category' => $category,
            'topic' => $topic,
            'form' => $form,
        ]);
    }
}
