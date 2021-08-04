<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\StravaAthleteRepository;
use App\Shared\StravaAPICalls;
use http\Client\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HomeController extends AbstractController
{
    private $articleRepo;
    private StravaAthleteRepository $stravaAthleteRepository;

    public function __construct(ArticleRepository $articleRepo, StravaAthleteRepository $stravaAthleteRepository)
    {
        $this->articleRepo = $articleRepo;
        $this->stravaAthleteRepository = $stravaAthleteRepository;
    }
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        $articles = $this->articleRepo->findAll();

        $athletes = $this->stravaAthleteRepository->findAll();
        foreach($athletes as $athlete){
            $latestActivity = StravaAPICalls::getLatestActivity($athlete);
            $athlete->setLatestActivityName($latestActivity->name);
            $athlete->setLatestActivityId($latestActivity->id);
            $athleteData = StravaAPICalls::getAthleteData($athlete);
            $athlete->setName($athleteData->firstname);
            $this->getDoctrine()->getManager()->persist($athlete);
            $this->getDoctrine()->getManager()->flush();
        }


        return $this->render('home/index.html.twig', [
            'athletes' => $athletes,
            'articles' => $articles,
        ]);
    }

}
