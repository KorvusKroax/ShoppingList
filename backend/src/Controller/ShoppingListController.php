<?php

namespace App\Controller;

use App\Entity\ShoppingList;
use App\Repository\ShoppingListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/shopping_lists')]
class ShoppingListController extends AbstractController
{
    public function __construct(
        private readonly ShoppingListRepository $shoppingListRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer
    ) { }

    #[Route(name: 'app_shopping_list_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $lists = $this->shoppingListRepository->findAll();
        // findAll() helyett findBy(['owner' => $this->getUser()])

        return $this->json($lists, Response::HTTP_OK, [], ['groups' => ['list:read']]);
    }

    #[Route(name: 'app_shopping_list_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = $request->getContent();
        $list = $this->serializer->deserialize($data, ShoppingList::class, 'json');
        $list->setOwner($this->getUser());

        $this->entityManager->persist($list);
        $this->entityManager->flush();

        return $this->json($list, Response::HTTP_CREATED, [], ['groups' => ['list:read']]);
    }

    #[Route('/{id}', name: 'app_shopping_list_read', methods: ['GET'])]
    public function read(ShoppingList $list): JsonResponse
    {
        if ($list->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Access denied. You do not own this list.');
        }

        return $this->json($list, Response::HTTP_OK, [], ['groups' => ['list:read']]);
    }

    #[Route('/{id}', name: 'app_shopping_list_update', methods: ['PATCH'])]
    public function update(ShoppingList $list, Request $request): JsonResponse
    {
        if ($list->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Access denied. You do not own this list.');
        }

        $updatedList = $this->serializer->deserialize($request->getContent(), ShoppingList::class,  'json', ['object_to_populate' => $list]);

        $this->entityManager->flush();

        return $this->json($updatedList, Response::HTTP_OK, [], ['groups' => ['list:read']]);
    }

    #[Route('/{id}', name: 'app_shopping_list_delete', methods: ['DELETE'])]
    public function delete(ShoppingList $list): JsonResponse
    {
        if ($list->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Access denied. You do not own this list.');
        }

        $this->entityManager->remove($list);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
