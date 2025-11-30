<?php

namespace App\Entity;

use App\Repository\ShoppingListItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ShoppingListItemRepository::class)]
#[ORM\Table(name: 'shopping_list_item')]
class ShoppingListItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['list:read', 'item:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['list:read', 'item:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['list:read', 'item:read'])]
    private ?string $quantity = null;

    #[ORM\Column(options: ['default' => false])]
    #[Groups(['list:read', 'item:read'])]
    private ?bool $is_completed = false;

    #[ORM\ManyToOne(inversedBy: 'shopping_list_items')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['item:read'])]
    private ?ShoppingList $shopping_list = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(?string $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getIsCompleted(): ?bool
    {
        return $this->is_completed;
    }

    public function setIsCompleted(bool $isCompleted): static
    {
        $this->is_completed = $isCompleted;
        return $this;
    }

    public function getShoppingList(): ?ShoppingList
    {
        return $this->shopping_list;
    }

    public function setShoppingList(?ShoppingList $shoppingList): static
    {
        $this->shopping_list = $shoppingList;
        return $this;
    }
}
