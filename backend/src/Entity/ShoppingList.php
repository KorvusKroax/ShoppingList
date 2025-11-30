<?php

namespace App\Entity;

use App\Repository\ShoppingListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ShoppingListRepository::class)]
#[ORM\Table(name: 'shopping_list')]
class ShoppingList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['list:read', 'item:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['list:read', 'item:read'])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'shopping_lists')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['list:read'])]
    private ?User $owner = null;

    #[ORM\OneToMany(mappedBy: 'shopping_list', targetEntity: ShoppingListItem::class, orphanRemoval: true)]
    #[Groups(['list:read'])]
    private Collection $shopping_list_items;

    public function __construct()
    {
        $this->shopping_list_items = new ArrayCollection();
    }

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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, ShoppingListItem>
     */
    public function getShoppingListItems(): Collection
    {
        return $this->shopping_list_items;
    }

    public function addShoppingListItem(ShoppingListItem $shoppingListItem): static
    {
        if (!$this->shopping_list_items->contains($shoppingListItem)) {
            $this->shopping_list_items->add($shoppingListItem);
            if ($shoppingListItem->getShoppingList() !== $this) {
                $shoppingListItem->setShoppingList($this);
            }
        }

        return $this;
    }

    public function removeItem(ShoppingListItem $shoppingListItem): static
    {
        if ($this->shopping_list_items->removeElement($shoppingListItem)) {
            if ($shoppingListItem->getShoppingList() === $this) {
                $shoppingListItem->setShoppingList(null);
            }
        }

        return $this;
    }
}
