<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class AuthorController extends AbstractController
{
    #[Route('/api/author/{id}', name: 'detail_author', methods: ["GET"])]
    public function getAuthor(int $id, AuthorRepository $author, SerializerInterface $serializer): JsonResponse
    {
        $author = $author->find($id);
        if ($author) {
            $jsonAuthor = $serializer->serialize($author, 'json', ['groups' => 'getAuthor']);
            return new JsonResponse($jsonAuthor, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/authors', name: 'all_authors', methods: ["GET"])]
    public function getAllAuthor(AuthorRepository $author, SerializerInterface $serializer): JsonResponse
    {
        $authors = $author->findAll();
        $jsonAllAuthors = $serializer->serialize($authors, 'json', ['groups' => 'getAuthor']);
        return new JsonResponse($jsonAllAuthors, Response::HTTP_OK, [], true);
    }

    #[Route("/api/author", name: "createAuthor", methods: ["POST"])]
    public function createAuthor(
        SerializerInterface $serializer,
        Request $request,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $newAuthor = $serializer->deserialize($request->getContent(), Author::class, "json");
        $content = $request->toArray();
        $nameAuthor = $content["name"] ?? null;
        $firstNameAuthor = $content["firstName"] ?? null;
        $newAuthor->setFirstName($firstNameAuthor);
        $newAuthor->setName($nameAuthor);

        $jsonAuthor = $serializer->serialize($newAuthor, "json");

        $entityManager->persist($newAuthor);
        $entityManager->flush();

        return new JsonResponse($jsonAuthor, Response::HTTP_CREATED, [], true);
    }
}
