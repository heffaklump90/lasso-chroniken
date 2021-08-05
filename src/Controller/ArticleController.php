<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    private $articleRepo;

    public function __construct(ArticleRepository $articleRepo)
    {
        $this->articleRepo = $articleRepo;
    }

    /**
     * @Route("/articles", name="articles")
     */
    public function articles(): Response
    {
        $articles = $this->articleRepo->findAll();
        return $this->render(
            'article/all.html.twig',
            [
                'articles' => $articles,
            ]
        );
    }

    /**
     * @Route("/article/{id}", name="article")
     */
    public function index(int $id): Response
    {
        $article = $this->articleRepo->find($id);
        return $this->render('article/index.html.twig', [
            'article' => $article,
        ]);
    }
}
