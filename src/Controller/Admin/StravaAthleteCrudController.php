<?php

namespace App\Controller\Admin;

use App\Entity\StravaAthlete;
use App\Exception\RedirectException;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class StravaAthleteCrudController extends AbstractCrudController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $stack)
    {
        $this->requestStack = $stack;
    }

    public static function getEntityFqcn(): string
    {
        return StravaAthlete::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            NumberField::new('clientId'),
            TextField::new('clientSecret')->onlyOnForms(),
            TextField::new('authorizationCode')->onlyOnForms(),
            TextField::new('refreshToken')->onlyOnForms(),
            TextField::new('authToken')->onlyOnForms(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);
        $session = $this->requestStack->getSession();
        $session->set('current_strava_athlete', $entityInstance->getId());
        throw new RedirectException(
            new RedirectResponse(sprintf('https://www.strava.com/oauth/authorize?client_id=%d&response_type=code&redirect_uri=%s&approval_prompt=force&scope=read',
                $entityInstance->getClientId(),
                "https://localhost:8000/exchange_token",
            ))
        );
    }
}
