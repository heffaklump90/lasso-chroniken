<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\StravaAthleteRepository;
use App\Service\StravaDataPersistence;
use App\Service\StravaAPICalls;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private $articleRepo;
    private StravaAthleteRepository $stravaAthleteRepository;
    private StravaDataPersistence $stravaDataPersistence;
    private StravaAPICalls $stravaAPICalls;

    public function __construct(ArticleRepository $articleRepo,
                                StravaAthleteRepository $stravaAthleteRepository,
                                StravaDataPersistence $stravaDataPersistence,
                                StravaAPICalls $stravaAPICalls)
    {
        $this->articleRepo = $articleRepo;
        $this->stravaAthleteRepository = $stravaAthleteRepository;
        $this->stravaDataPersistence = $stravaDataPersistence;
        $this->stravaAPICalls = $stravaAPICalls;
    }
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        $articles = $this->articleRepo->findAll();

        $athletes = $this->stravaAthleteRepository->findAll();
        foreach($athletes as $athlete){
            $latestActivity = $this->stravaAPICalls->getLatestActivity($athlete);
            $this->stravaDataPersistence->saveLatestActivityData($athlete, $latestActivity);

            $athleteData = $this->stravaAPICalls->getAthleteData($athlete);
            $this->stravaDataPersistence->saveAthleteData($athlete, $athleteData);
        }

        return $this->render('home/index.html.twig', [
            'athletes' => $athletes,
            'articles' => $articles,
        ]);
    }

}
