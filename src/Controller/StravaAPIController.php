<?php

namespace App\Controller;

use App\Repository\StravaAthleteRepository;
use App\Shared\StravaAPICalls;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StravaAPIController extends AbstractController
{
    private $logger;
    private StravaAthleteRepository $stravaAthleteRepository;

    public function __construct(LoggerInterface $logger, StravaAthleteRepository $stravaAthleteRepository)
    {
        $this->logger = $logger;
        $this->stravaAthleteRepository = $stravaAthleteRepository;
    }

    /**
     * @Route("/exchange_token", name="strava_exchange_token")
     */
    public function exchangeToken(Request $request, RequestStack $stack): Response
    {
        $session = $stack->getSession();
        $stravaAthlete = $this->stravaAthleteRepository->find($session->get('current_strava_athlete'));
        $stravaAthlete->setAuthorizationCode($request->get('code'));

        $em = $this->getDoctrine()->getManager();

        $stravaData = StravaAPICalls::getAuthCode( $stravaAthlete );

        $stravaAthlete->setAuthToken($stravaData->access_token);
        $stravaAthlete->setRefreshToken($stravaData->refresh_token);
        $stravaAthlete->setTokenExpiryTime( new \DateTime("@" . $stravaData->expires_at) );

        $em->persist($stravaAthlete);
        $em->flush();

        $athleteData = StravaAPICalls::getAthleteData($stravaAthlete);
        $stravaAthlete->setName($athleteData->firstname);

        $em->persist($stravaAthlete);
        $em->flush();

        $stravaActivityData = StravaAPICalls::getLatestActivity($stravaAthlete);
        $stravaAthlete->setLatestActivityId($stravaActivityData->id);
        $stravaAthlete->setLatestActivityName($stravaActivityData->name);

        $em->persist($stravaAthlete);
        $em->flush();

        return $this->render('strava_api/exchange_token.html.twig', [
            'athlete' => $stravaAthlete,
        ]);
    }

    /**
     * @Route("/test_api", name="strava_test_api")
     */
    public function index(): Response
    {
        return $this->render('strava_api/index.html.twig');
    }

    /**
     * @Route("/strava-refresh-token", name="strava_refresh_auth_token")
     */
    public function refreshToken(): Response
    {
        $athletes = $this->stravaAthleteRepository->findAll();
        $data = array();

        foreach($athletes as $athlete){
            $data[] = StravaAPICalls::refreshAuthToken($athlete, $this->getDoctrine()->getManager());
        }
        return $this->render('strava_api/index.html.twig', [
            'data' => $data,
        ]);
    }

    /**
     * @Route("/strava-athlete-data", name="strava_athlete_data")
     */
    public function athlete(): Response
    {
        $athlete = $this->stravaAthleteRepository->findOneBy(['clientId' => 68910]);
        $data = StravaAPICalls::getAthleteData($athlete, $this->getDoctrine()->getManager());
        return $this->render('strava_api/index.html.twig', [
            'data' => $data,
        ]);
    }

    /**
     * @Route("/strava-activities", name="strava_activities_data")
     */
    public function activities(): Response
    {
        $athlete = $this->stravaAthleteRepository->findOneBy(['clientId' => 68910]);
        $data = StravaAPICalls::getActivities($athlete);
        return $this->render('strava_api/index.html.twig', [
            'data' => $data,
        ]);
    }

    /**
     * @Route("/latest-strava-activity", name="strava_latest_activity_data")
     */
    public function latestActivity(): Response
    {
        $athlete = $this->stravaAthleteRepository->findOneBy(['clientId' => 68910]);
        $data = StravaAPICalls::getLatestActivity($athlete);
        return $this->render('strava_api/index.html.twig', [
            'data' => $data,
        ]);

    }

    /**
     * @Route("/strava-authorize-athlete", name="strava_authorize_athlete")
     */
    public function authorizeAthlete(): Response
    {
        return $this->render('strava_api/index.html.twig');
    }
}
