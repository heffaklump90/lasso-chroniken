<?php


namespace App\Listener;


use App\Entity\Article;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\AsciiSlugger;

class ArticleEventListener
{
    public function prePersist(Article $article, LifecycleEventArgs $args): void
    {
        $slugger = new AsciiSlugger();
        $article->setSlug(strtolower($slugger->slug($article->getTitle())));
    }

}