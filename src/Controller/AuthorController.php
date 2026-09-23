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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AuthorController extends AbstractController
{
    #[Route('/api/author/{id}', name: 'getAuthor', methods: ["GET"])]
    public function getAuthor(int $id, AuthorRepository $author, SerializerInterface $serializer): JsonResponse
    {
        $author = $author->find($id);
        if ($author) {
            $jsonAuthor = $serializer->serialize($author, 'json', ['groups' => 'getAuthor']);
            return new JsonResponse($jsonAuthor, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/authors', name: 'getAllAuthor', methods: ["GET"])]
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
        ValidatorInterface $validator
    ): JsonResponse {
        $newAuthor = $serializer->deserialize($request->getContent(), Author::class, "json");

        $errors = $validator->validate($newAuthor);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, "json"), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

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

    #[Route("/api/author/{id}", name: "updateAuthor", methods: ["PUT"])]
    public function updateAuthor(
        SerializerInterface $serializer,
        Request $request,
        EntityManagerInterface $entityManager,
        Author $currentAuthor
    ): JsonResponse {
        $updateAuthor = $serializer->deserialize($request->getContent(), Author::class, "json", [AbstractNormalizer::OBJECT_TO_POPULATE => $currentAuthor]);
        $content = $request->toArray();
        $nameAuthor = $content["name"] ?? null;
        $firstNameAuthor = $content["firstName"] ?? null;
        $updateAuthor->setFirstName($firstNameAuthor);
        $updateAuthor->setName($nameAuthor);

        $entityManager->persist($updateAuthor);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route("/api/author/{id}", name: "deleteAuthor", methods: ["DELETE"])]
    public function deleteAuthor(Author $author, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($author);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
