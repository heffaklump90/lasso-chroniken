<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $imageFile = TextareaField::new('userImageFile')->setFormType(VichImageType::class);
        $image = ImageField::new('userImage')->setBasePath('/upload/img');
        $fields = [
            TextField::new('email'),
            TextField::new('firstName'),
            TextField::new('lastName'),
            AssociationField::new('stravaAthlete')->onlyOnForms(),
            CollectionField::new('roles'),
            BooleanField::new('isVerified'),
        ];

        if($pageName == Crud::PAGE_INDEX || $pageName == Crud::PAGE_DETAIL){
            $fields[] = $image;
        }
        else{
            $fields[] = $imageFile;
        }

        return $fields;
    }


}
