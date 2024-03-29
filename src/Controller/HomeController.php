<?php

namespace App\Controller;

use App\Exception\AuthTokenExpiredException;
use App\Repository\ArticleRepository;
use App\Repository\StravaAthleteRepository;
use App\Service\StravaDataPersistence;
use App\Service\StravaAPICalls;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private ArticleRepository $articleRepo;
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
        $articles = $this->articleRepo->findPlainArticles();

        $raceArticles = $this->articleRepo->findRaceArticles();

        $athleteViewData = $this->getAthleteViewData();

        return $this->render('home/index.html.twig', [
            'athleteViewData' => $athleteViewData,
            'raceArticles' => $raceArticles,
            'articles' => $articles,
        ]);
    }

    public function getAthleteViewData()
    {
        $athletes = $this->stravaAthleteRepository->findAll();
        $athleteViewData = array();
        foreach($athletes as $athlete) {
            $latestActivity = $this->stravaAPICalls->getLatestActivity($athlete);
            $this->stravaDataPersistence->saveLatestActivityData($athlete, $latestActivity);

            $athleteData = $this->stravaAPICalls->getAthleteData($athlete);
            $this->stravaDataPersistence->saveAthleteData($athlete, $athleteData);

            $photo = "";
            if ($latestActivity->total_photo_count > 0) {
                $photo = $latestActivity->photos->primary->urls->{100};
            }

            $athleteViewData[] = [
                'name' => $athlete->getName(),
                'latest_activity_name' => $latestActivity->name,
                'latest_activity_uri' => sprintf(StravaAPICalls::STRAVA_WEB_ACTIVITY_URI, $latestActivity->id),
                'profile_picture' => $athlete->getProfileMedium(),
                'photo' => $photo,
            ];
        }
        return $athleteViewData;
    }

}
