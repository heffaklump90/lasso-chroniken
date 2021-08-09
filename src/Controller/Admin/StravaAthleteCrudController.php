<?php

namespace App\Controller\Admin;

use App\Entity\StravaAthlete;
use App\Exception\RedirectException;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
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
            TextField::new('name')
                ->setFormTypeOption('disabled', true),
            TextareaField::new('latestActivityData')->onlyOnForms()
                ->setFormTypeOption('disabled', true),
            TextField::new('authorizationCode')->onlyOnForms()
                ->setFormTypeOption('disabled', true),
            TextField::new('refreshToken')->onlyOnForms()
                ->setFormTypeOption('disabled', true),
            TextField::new('authToken')->onlyOnForms()
                ->setFormTypeOption('disabled', true),
            DateTimeField::new('tokenExpiryTime')->onlyOnForms()
                ->setFormTypeOption('disabled', true),
            TextField::new('profile')->onlyOnForms()
                ->setFormTypeOption('disabled', true),
            TextField::new('profileMedium')->onlyOnForms()
                ->setFormTypeOption('disabled', true),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);
        $session = $this->requestStack->getSession();

        $session->set('current_strava_athlete', $entityInstance->getId());
        throw new RedirectException(
            new RedirectResponse(sprintf('https://www.strava.com/oauth/authorize?client_id=%d&response_type=code&redirect_uri=%s&approval_prompt=force&scope=read,activity:read',
                $entityInstance->getClientId(),
                $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost() . "/exchange_token",
            ))
        );
    }
}
