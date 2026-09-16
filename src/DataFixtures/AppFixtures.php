<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        /**
         * Création des auteurs
         */
        $listAuthors = [];
        for ($i = 0; $i < 10; $i++) {
            //Création de l'auteur
            $author = new Author();
            $author->setName("Prénom de l'auteur: " . $i);
            $author->setFirstName("Nom de l'auteur: " . $i);
            $manager->persist($author);

            $listAuthors[] = $author;
        }

        for ($i = 0; $i < 20; $i++) {
            $book = new Book();
            $book->setTitle("Titre n°: " . $i);
            $book->setCoverText("Couverture n°: " . $i);
            $book->setAuthor($listAuthors[array_rand($listAuthors)]);
            $manager->persist($book);
        }

        $manager->flush();
    }
}
