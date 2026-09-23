<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class BookController extends AbstractController
{
    #[Route('/api/books', name: 'allBooks', methods: ['GET'])]
    public function getAllBooks(BookRepository $bookRepository, SerializerInterface $serializer): JsonResponse
    {
        $bookList = $bookRepository->findAll();
        $jsonBookList = $serializer->serialize($bookList, 'json', ["groups" => "getBooks"]);

        return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/book/{id}', name: 'detailBook', methods: ['GET'])]
    public function getBookById(int $id, BookRepository $bookRepository, SerializerInterface $serializer): JsonResponse
    {
        $bookById = $bookRepository->find($id);
        if ($bookById) {
            $jsonBookById = $serializer->serialize($bookById, 'json', ["groups" => "getBooks"]);

            return new JsonResponse($jsonBookById, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/book/{id}', name: 'deleteBook', methods: ["DELETE"])]
    public function deleteBookById(Book $book, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($book);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/book', name: 'addBook', methods: ["POST"])]
    public function createBook(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        AuthorRepository $authorRepository,
        ValidatorInterface $validator
    ): JsonResponse {
        $book = $serializer->deserialize($request->getContent(), Book::class, 'json');
        $errors = $validator->validate($book);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, "json"), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($book);
        $entityManager->flush();
        
        //Récupération des données sous formes de tableau
        $content = $request->toArray();
        $authorId = $content['idAuthor'] ?? -1;
        $book->setAuthor($authorRepository->find($authorId));


        $jsonNewBook = $serializer->serialize($book, 'json', ['groups' => "getBooks"]);
        $location = $urlGenerator->generate('detailBook', ['id' => $book->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonNewBook, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/book/{id}', name: "updateBook", methods: ["PUT"])]
    public function updateBook(
        SerializerInterface $serializer,
        Book $currentBook,
        EntityManagerInterface $entityManager,
        Request $request,
        AuthorRepository $authorRepository
    ): JsonResponse {
        $updateBook = $serializer->deserialize($request->getContent(), Book::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentBook]);
        $content = $request->toArray();
        $authorId = $content["idAuthor"] ?? -1;
        $updateBook->setAuthor($authorRepository->find($authorId));

        $entityManager->persist($updateBook);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
