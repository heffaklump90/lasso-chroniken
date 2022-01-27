<?php

namespace App\Controller\Admin;

use App\Entity\StravaActivity;
use App\Service\StravaAPICalls;
use App\Service\StravaDataPersistence;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response;

class StravaActivityCrudController extends AbstractCrudController
{
    private StravaAPICalls $stravaAPICalls;
    private StravaDataPersistence $stravaDataPersistence;
    public function __construct(StravaAPICalls $stravaAPICalls, StravaDataPersistence $stravaDataPersistence)
    {
        $this->stravaAPICalls = $stravaAPICalls;
        $this->stravaDataPersistence = $stravaDataPersistence;
    }

    public static function getEntityFqcn(): string
    {
        return StravaActivity::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield TextField::new('name');

        $user = $this->getUser();
        $athlete = $user->getStravaAthlete();
        if(null !== $athlete) {
            $activities = $this->stravaAPICalls->getActivities($athlete);
            $choices = array_map(fn($activity): array => [$activity->name => $activity->id], $activities);

            yield ChoiceField::new('stravaId')
                ->onlyWhenCreating()
                ->setChoices($choices);
        }
        yield TextareaField::new('data')->onlyOnForms()
            ->setFormTypeOption('disabled', 'disabled');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $user = $this->getUser();
        $athlete = $user->getStravaAthlete();
        $detailedData = $this->stravaAPICalls->getActivityDetail($athlete, $entityInstance->getStravaId());
        $entityInstance->setData(json_encode($detailedData));
        $entityManager->flush();
        parent::persistEntity($entityManager, $entityInstance);
    }
}
