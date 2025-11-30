<?php

namespace App\Controller;

use App\Entity\ShoppingList;
use App\Entity\ShoppingListItem;
use App\Repository\ShoppingListItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ShoppingListItemController extends AbstractController
{
    public function __construct(
        private readonly ShoppingListItemRepository $shoppingListItemRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer
    ) { }

    #[Route('/api/shopping_list_items', name: 'app_shopping_list_item_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $items = $this->shoppingListItemRepository->findAll();

        return $this->json($items, Response::HTTP_OK, [], ['groups' => ['list:read']]);
    }

    #[Route('/api/shopping_lists/{listId}/shopping_list_items', name: 'app_shopping_list_item_create', methods: ['POST'])]
    public function create(int $listId, Request $request): JsonResponse
    {
        if (!$this->getUser() instanceof UserInterface) {
            return new JsonResponse(['message' => 'User not authenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $list = $this->entityManager->getRepository(ShoppingList::class)->find($listId);
        if (!$list) {
            return $this->json(['message' => 'ShoppingList not found.'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->getContent();
        $item = $this->serializer->deserialize($data, ShoppingListItem::class, 'json');

        $item->setShoppingList($list);

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $this->json($item, Response::HTTP_CREATED, [], ['groups' => ['item:read']]);
    }

    #[Route('/api/shopping_list_items/{id}', name: 'app_shopping_list_item_read', methods: ['GET'])]
    public function read(ShoppingListItem $item): JsonResponse
    {
        $list = $item->getShoppingList();
        if (!$this->getUser() instanceof UserInterface || $list->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Access denied. You do not own the parent shopping list.');
        }

        return $this->json($item, Response::HTTP_OK, [], ['groups' => 'item:read']);
    }

    #[Route('/api/shopping_list_items/{id}', name: 'app_shopping_list_item_update', methods: ['PATCH'])]
    public function update(ShoppingListItem $item, Request $request): JsonResponse
    {
        $list = $item->getShoppingList();
        if (!$this->getUser() instanceof UserInterface || $list->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Access denied. You do not own the parent shopping list.');
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $item->setName($data['name']);
        }
        if (isset($data['quantity'])) {
            $item->setQuantity($data['quantity']);
        }
        if (isset($data['isCompleted'])) {
            $item->setIsCompleted((bool)$data['isCompleted']);
        }
        // -- lista változtatás

        $this->entityManager->flush();

        return $this->json($item, Response::HTTP_OK, [], ['groups' => ['item:read']]);
    }

    #[Route('/api/shopping_list_items/{id}', name: 'app_shopping_list_item_delete', methods: ['DELETE'])]
    public function delete(ShoppingListItem $item): JsonResponse
    {
        $list = $item->getShoppingList();
        if (!$this->getUser() instanceof UserInterface || $list->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Access denied. You do not own the parent shopping list.');
        }

        $this->entityManager->remove($item);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
