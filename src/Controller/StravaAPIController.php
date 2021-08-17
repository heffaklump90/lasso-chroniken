<?php

namespace App\Controller;

use App\Exception\AuthTokenExpiredException;
use App\Repository\StravaAthleteRepository;
use App\Service\StravaDataPersistence;
use App\Service\StravaAPICalls;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StravaAPIController extends AbstractController
{

    private $logger;
    private StravaAthleteRepository $stravaAthleteRepository;
    private StravaDataPersistence $stravaDataPersistence;
    private StravaAPICalls $stravaAPICalls;

    public function __construct(LoggerInterface $logger,
                                StravaAthleteRepository $stravaAthleteRepository,
                                StravaDataPersistence $dataPersistence,
                                StravaAPICalls $stravaAPICalls)
    {
        $this->logger = $logger;
        $this->stravaAthleteRepository = $stravaAthleteRepository;
        $this->stravaDataPersistence = $dataPersistence;
        $this->stravaAPICalls = $stravaAPICalls;
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
        $em->persist($stravaAthlete);
        $em->flush();

        $stravaData = $this->stravaAPICalls->getAuthCode( $stravaAthlete );
        $this->stravaDataPersistence->saveAuthData($stravaAthlete, $stravaData);

        $athleteData = $this->stravaAPICalls->getAthleteData( $stravaAthlete );
        $this->stravaDataPersistence->saveAthleteData(  $stravaAthlete, $athleteData );

        $stravaActivityData = $this->stravaAPICalls->getLatestActivity( $stravaAthlete );
        $this->stravaDataPersistence->saveLatestActivityData( $stravaAthlete, $stravaActivityData );

        return $this->render('strava_api/exchange_token.html.twig', [
            'athlete' => $stravaAthlete,
        ]);
    }


    /**
     * @Route("/test-api", name="strava_test_api")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(Request $request): Response
    {
        $athletes = $this->stravaAthleteRepository->findAll();
        $choices = array();
        foreach($athletes as $athlete){
            $choices[$athlete->getName()] = $athlete->getClientId();
        }
        $form = $this->createFormBuilder()
            ->add('clientId', ChoiceType::class, [
                'choices' => $choices,
            ])
            ->add('refreshAuthToken', SubmitType::class)
            ->add('getActivities', SubmitType::class)
            ->add('getLatestActivity', SubmitType::class)
            ->add('getAthleteData', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        $stravaData = array();

        $latestActivity = false;
        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $apiCallName = $form->getClickedButton()->getName();
            if(0 === strcmp( $apiCallName, "getLatestActivity" )){
                $latestActivity = true;
            }
            $athlete = $this->stravaAthleteRepository->findOneBy(['clientId' => $data['clientId']]);
            try {
                $stravaData = $this->stravaAPICalls->$apiCallName($athlete);
            } catch (ClientException $exception){
                if($exception->getCode() == Response::HTTP_UNAUTHORIZED ) {
                    $refreshTokenData = $this->stravaAPICalls->refreshAuthToken($athlete);
                    $this->stravaDataPersistence->saveRefreshTokenData($athlete, $refreshTokenData);
                } else {
                    throw $exception;
                }
                $stravaData = $this->stravaAPICalls->$apiCallName($athlete);
            }
        }

        return $this->render('strava_api/index.html.twig', [
            'form' => $form->createView(),
            'stravaData' => $stravaData,
            'athletes' => $athletes,
            'latestActivity' => $latestActivity,
        ]);
    }

    /**
     * @Route("/strava-refresh-token", name="strava_refresh_auth_token")
     */
    public function refreshToken(): Response
    {
        $athletes = $this->stravaAthleteRepository->findAll();
        $data = array();

        foreach($athletes as $athlete){
            $data[] = $this->stravaAPICalls->refreshAuthToken($athlete);
            $this->stravaDataPersistence->saveRefreshTokenData($athlete, end($data));
        }
        return $this->render('strava_api/index.html.twig', [
            'athletes' => $athletes,
            'data' => $data,
        ]);
    }







}
