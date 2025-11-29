<?php

namespace App\Controller;

use App\Entity\User; // Ideiglenesen a teszt userhez
use App\Entity\ShoppingList;
use App\Repository\ShoppingListRepository;
use Doctrine\ORM\EntityManagerInterface; // EntityManager a mentéshez
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface; // A JSON dekódolásához

#[Route('/api/shopping_lists')]
class ShoppingListController extends AbstractController
{
    public function __construct(
        private readonly ShoppingListRepository $shoppingListRepository,
        private readonly EntityManagerInterface $entityManager, // Az entitások kezeléséhez
        private readonly SerializerInterface $serializer // JSON-ből objektummá alakításhoz
    ) {
    }

    // 1. GET /api/shopping_lists (LISTÁZÁS) - KÉSZ
    #[Route(name: 'app_shopping_list_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        // Jelenleg az összes listát lekérdezzük. Később ez a felhasználó listáira szűkül.
        $lists = $this->shoppingListRepository->findAll();

        return $this->json(
            $lists,
            Response::HTTP_OK,
            [],
            ['groups' => ['list:read']]
        );
    }

    // 2. GET /api/shopping_lists/{id} (MEGJELENÍTÉS ID ALAPJÁN)
    #[Route('/{id}', name: 'app_shopping_list_show', methods: ['GET'])]
    public function show(ShoppingList $list): JsonResponse
    {
        // A Symfony automatikusan betölti a ShoppingList entitást az ID alapján (ParamConverter)
        return $this->json(
            $list,
            Response::HTTP_OK,
            [],
            ['groups' => ['list:read']]
        );
    }

    // 3. POST /api/shopping_lists (LÉTREHOZÁS)
    #[Route(name: 'app_shopping_list_new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        // JSON dekódolása
        $data = $request->getContent();

        // 1. A JSON átalakítása ShoppingList objektummá
        $list = $this->serializer->deserialize($data, ShoppingList::class, 'json');

        // 2. Ideiglenes Owner beállítása (1 ID-val feltételezzük)
        // Ezt a részt a Security beállítása után ki kell cserélni: $this->getUser()
        $tempOwner = $this->entityManager->getReference(User::class, 1);
        $list->setOwner($tempOwner);

        // 3. Mentés
        $this->entityManager->persist($list);
        $this->entityManager->flush();

        return $this->json(
            $list,
            Response::HTTP_CREATED, // 201 Created
            [],
            ['groups' => ['list:read']]
        );
    }

    // 4. DELETE /api/shopping_lists/{id} (TÖRLÉS)
    #[Route('/{id}', name: 'app_shopping_list_delete', methods: ['DELETE'])]
    public function delete(ShoppingList $list): JsonResponse
    {
        // A Valódi kód itt ellenőrizné, hogy a lista a bejelentkezett felhasználóé-e

        $this->entityManager->remove($list);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT); // 204 No Content
    }
}
