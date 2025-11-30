<?php

namespace App\Controller;

use App\Entity\User; // Ideiglenesen a teszt userhez

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

        return $this->json($lists, Response::HTTP_OK, [], ['groups' => ['list:read']]);
    }

    #[Route('/{id}', name: 'app_shopping_list_show', methods: ['GET'])]
    public function show(ShoppingList $list): JsonResponse
    {
        return $this->json($list, Response::HTTP_OK, [], ['groups' => ['list:read']]);
    }

    #[Route(name: 'app_shopping_list_new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $data = $request->getContent();

        $list = $this->serializer->deserialize($data, ShoppingList::class, 'json');

        // Ideiglenes Owner beállítása (1 ID-val feltételezzük)
        // Ezt a részt a Security beállítása után ki kell cserélni: $this->getUser()
        $tempOwner = $this->entityManager->getReference(User::class, 1);
        $list->setOwner($tempOwner);

        $this->entityManager->persist($list);
        $this->entityManager->flush();

        return $this->json($list, Response::HTTP_CREATED, [], ['groups' => ['list:read']]);
    }

    #[Route('/{id}', name: 'app_shopping_list_delete', methods: ['DELETE'])]
    public function delete(ShoppingList $list): JsonResponse
    {
        // A Valódi kód itt ellenőrizné, hogy a lista a bejelentkezett felhasználóé-e

        $this->entityManager->remove($list);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
