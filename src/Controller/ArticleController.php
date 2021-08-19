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
        $polyLine = null;
        if($article->hasStravaActivity()){
            $activityData = json_decode($article->getStravaActivity()->getData());
            $polyLine['full'] = $activityData->map->polyline;
            $polyLine['summary'] = $activityData->map->summary_polyline;
        }

        $templateVars = ['article' => $article];
        if($polyLine){
            $templateVars['polyLine'] = $polyLine;
        }
        return $this->render('article/index.html.twig', $templateVars);
    }
}
