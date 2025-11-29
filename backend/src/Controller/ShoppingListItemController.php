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

class ShoppingListItemController extends AbstractController
{
    public function __construct(
        private readonly ShoppingListItemRepository $shoppingListItemRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer
    ) {
    }

    // 0. GET /api/shopping_list_items (LISTÁZÁS) - KÉSZ
    #[Route('/api/shopping_list_items', name: 'app_shopping_list_item_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        // Jelenleg az összes listát lekérdezzük. Később ez a felhasználó listáira szűkül.
        $items = $this->shoppingListItemRepository->findAll();

        return $this->json(
            $items,
            Response::HTTP_OK,
            [],
            ['groups' => ['list:read']]
        );
    }

    // 1. POST /api/shopping_lists/{listId}/shopping_list_items (ÚJ TÉTEL LÉTREHOZÁSA)
    #[Route('/api/shopping_lists/{listId}/shopping_list_items', name: 'app_shopping_list_item_new', methods: ['POST'])]
    public function new(int $listId, Request $request): JsonResponse
    {
        // 1. Megkeresi a szülő listát
        $list = $this->entityManager->getRepository(ShoppingList::class)->find($listId);

        if (!$list) {
            return $this->json(['message' => 'ShoppingList not found.'], Response::HTTP_NOT_FOUND); // 404
        }

        // 2. Deszerializáció
        $data = $request->getContent();
        $item = $this->serializer->deserialize($data, ShoppingListItem::class, 'json');

        // 3. Kapcsolat beállítása a szülő listával
        $item->setShoppingList($list);

        // 4. Mentés
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $this->json(
            $item,
            Response::HTTP_CREATED,
            [],
            ['groups' => ['item:read']]
        );
    }

    // 2. PATCH /api/shopping_list_items/{id} (FRISSÍTÉS - pl. IS_COMPLETED)
    #[Route('/api/shopping_list_items/{id}', name: 'app_shopping_list_item_update', methods: ['PATCH'])]
    public function update(ShoppingListItem $item, Request $request): JsonResponse
    {
        // A kérés tartalmát dekódoljuk
        $data = json_decode($request->getContent(), true);

        // Csak a 'isCompleted' mezőt frissítjük
        if (isset($data['isCompleted'])) {
            $item->setIsCompleted((bool)$data['isCompleted']);
        }
        // Vagy a 'name' és 'quantity' mezőket is
        if (isset($data['name'])) {
            $item->setName($data['name']);
        }
        if (isset($data['quantity'])) {
            $item->setQuantity($data['quantity']);
        }

        $this->entityManager->flush();

        return $this->json($item, Response::HTTP_OK, [], ['groups' => ['item:read']]);
    }

    // 3. DELETE /api/shopping_list_items/{id} (TÖRLÉS)
    #[Route('/api/shopping_list_items/{id}', name: 'app_shopping_list_item_delete', methods: ['DELETE'])]
    public function delete(ShoppingListItem $item): JsonResponse
    {
        $this->entityManager->remove($item);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT); // 204 No Content
    }
}
