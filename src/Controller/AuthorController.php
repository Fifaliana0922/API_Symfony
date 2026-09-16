<?php

namespace App\Controller;

use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
}
