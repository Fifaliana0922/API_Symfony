<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class BookController extends AbstractController
{
    #[Route('/api/books', name: 'app_book', methods: ['GET'])]
    public function getAllBooks(BookRepository $bookRepository, SerializerInterface $serializer): JsonResponse
    {
        $bookList = $bookRepository->findAll();
        $jsonBookList = $serializer->serialize($bookList, 'json');

        return new JsonResponse(
            $jsonBookList,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/api/book/{id}', name: 'detail_book', methods: ['GET'])]
    public function getBookById(int $id, BookRepository $bookRepository, SerializerInterface $serializer): JsonResponse
    {
        $bookById = $bookRepository->find($id);
        if ($bookById) {
            $jsonBookById = $serializer->serialize($bookById, 'json');
            return new JsonResponse(
                $jsonBookById,
                Response::HTTP_OK,
                [],
                true
            );
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
