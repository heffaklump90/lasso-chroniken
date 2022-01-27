<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail('admin@lasso-chroniken.de');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword("\$argon2id\$v=19\$m=65536,t=4,p=1\$FI2/4utIm6JpDNrU45/xyQ\$nmXvZWqXXzeN+7q3KYoRtQjEQJpVfncpwtjAdhDVR9A");
        $manager->persist($user);

        $author = new User();
        $author->setEmail('bjoerne@email.com');
        $author->setFirstName('Björn');
        $author->setLastName('Andersson');
        $author->setIsVerified(true);
        $author->setRoles(['ROLE_AUTHOR', 'ROLE_ADMIN']);
        $author->setPassword($this->passwordEncoder->encodePassword( $user, 'password'));

        $manager->persist($author);

        $article = new Article();
        $article->setAuthor($user);
        $article->setTitle("Erster Artikel");
        $article->setSlug("artikel-1");
        $article->setContent("<p>so, dass ist was</p>");
        $article->setPublishAt(new \DateTimeImmutable('now'));
        $manager->persist($article);

        $manager->flush();
    }
}
