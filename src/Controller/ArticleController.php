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
     * @Route("/artikel/{slug}", name="articles")
     */
    public function articles(string $slug = ""): Response
    {

        $articles = $this->articleRepo->findPlainArticles();
        $article = $this->articleRepo->findOneBy(['slug' => $slug]);
        if($article == null){
            $article = $articles[0];
        }
        return $this->render(
            'article/article.html.twig',
            [
                'article' => $article,
                'articles' => $articles,
            ]
        );
    }

    /**
     * @Route("/laufberichte/{slug}", name="race_reports")
     */
    public function raceReports(string $slug = ""): Response
    {
        $articles = $this->articleRepo->findRaceArticles();
        $article = $this->articleRepo->findOneBy(['slug'=> $slug]);
        if($article == null){
            $article = $articles[0];
        }
        $polyLine = null;
        if($article->hasStravaActivity()){
            $activityData = json_decode($article->getStravaActivity()->getData());
            $polyLine = array();
            $polyLine['full'] = $activityData->map->polyline;
            $polyLine['summary'] = $activityData->map->summary_polyline;
        }
        $templateVars = ['article' => $article, 'articles' => $articles];
        if($polyLine){
            $templateVars['polyLine'] = $polyLine;
        }
        return $this->render(
            'article/raceReport.html.twig',
            $templateVars,
        );
    }

    /**
     * @Route("/articlelisting", name="article_listing")
     */
    public function articleNavigation(): Response
    {
        $articles = $this->articleRepo->findPlainArticles();
        $raceArticles = $this->articleRepo->findRaceArticles();

        return $this->render('article/listing.html.twig',
        [
            'articles' => $articles,
            'raceArticles' => $raceArticles,
        ]);
    }
}
