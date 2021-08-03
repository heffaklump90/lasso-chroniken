<?php

namespace App\Controller;

use App\Repository\StravaAthleteRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StravaAPIController extends AbstractController
{
    const STRAVA_AUTH_URI = "https://www.strava.com/oauth/authorize";
    const STRAVA_SESSION_LOGIN_URI = "https://www.strava.com/session";

    private $logger;
    private StravaAthleteRepository $stravaAthleteRepository;

    public function __construct(LoggerInterface $logger, StravaAthleteRepository $stravaAthleteRepository)
    {
        $this->logger = $logger;
        $this->stravaAthleteRepository = $stravaAthleteRepository;
    }

    /**
     * @Route("/authorize", name="strava_authorize")
     */
    public function index( ): Response
    {
        $client = HttpClient::create( );
        $response = $client->request('GET', self::STRAVA_AUTH_URI, [
            'query' => [
                'client_id' => 68910,
                'response_type' => 'code',
                'redirect_uri' => 'https://localhost/exchange_token',
                'approval_prompt' => 'force',
                'scope' => 'read',
            ],
        ]);

        return $this->render('strava_api/index.html.twig', [
            'status' => $response->getStatusCode(),
            'curl_response' => $response->getContent(),
        ]);
    }

    /**
     * @Route("/session", name="strava_login_session")
     */
    public function session(Request $request): Response
    {
        $client = HttpClient::create();
        $queryString = $request->getQueryString();
        /*$response = $client->request('POST', self::STRAVA_SESSION_LOGIN_URI, [
            'query' => [
                'authenticity_token' => $request->getQueryString() getQuery('authenticity_token'),
                'email' => $request->getQuery('email'),
                'password' => $request->getQuery('password'),
                'plan' => $request->getQuery('plan'),
                'utf8' => $request->getQuery('utf8')

            ]
        ]);*/
        return $this->render('strava_api/index.html.twig', [
            'curl_response' => $response,
        ]);
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
        $client = HttpClient::create();

        $response = $client->request('POST', 'https://www.strava.com/oauth/token', ['query' => [
            'client_id' => $stravaAthlete->getClientId(),
            'client_secret' => $stravaAthlete->getClientSecret(),
            'code' => $stravaAthlete->getAuthorizationCode(),
            'grant_type' => 'authorization_code',
        ]]);

        $stravaData = json_decode($response->getContent());
        $stravaAthlete->setAuthToken($stravaData->access_token);
        $stravaAthlete->setRefreshToken($stravaData->refresh_token);
        $stravaAthlete->setTokenExpiryTime( new \DateTime("@" . $stravaData->expires_at) );

        $em->persist($stravaAthlete);
        $em->flush();

        return $this->render('strava_api/index.html.twig', [
            'athlete' => $stravaAthlete,
        ]);
    }
}
