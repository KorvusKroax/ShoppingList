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
    #[Groups(['list:read', 'item:read'])] // Lássuk az ID-t a listázásnál és a tétel lekérdezésnél
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['list:read', 'item:read'])] // Lássuk a listák nevét
    private ?string $name = null;

    // Kapcsolat a User entitással (owner)
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
     * @return Collection<int, LiShoppingListItemstItem>
     */
    // --- GETTER (Lekérdezi az összes tételt) ---
    public function getShoppingListItems(): Collection
    {
        return $this->shopping_list_items;
    }

    // --- ADDER (Hozzáad egy tételt a listához) ---
    public function addShoppingListItem(ShoppingListItem $shoppingListItem): static
    {
        if (!$this->shopping_list_items->contains($shoppingListItem)) {
            $this->shopping_list_items->add($shoppingListItem);
            // KÉTIRÁNYÚ KAPCSOLAT KEZELÉSE: Frissítjük a ShoppingListItem entitást is!
            if ($shoppingListItem->getShoppingList() !== $this) {
                $shoppingListItem->setShoppingList($this);
            }
        }

        return $this;
    }

    // --- REMOVER (Eltávolít egy tételt a listából) ---
    public function removeItem(ShoppingListItem $shoppingListItem): static
    {
        if ($this->shopping_list_items->removeElement($shoppingListItem)) {
            // KÉTIRÁNYÚ KAPCSOLAT KEZELÉSE:
            // Ha a ShoppingListItem a listához tartozott (null-ra állítjuk a FK-t),
            // de csak akkor, ha az entitás nem kerül törlésre (orphanRemoval: true)
            if ($shoppingListItem->getShoppingList() === $this) {
                $shoppingListItem->setShoppingList(null);
            }
        }

        return $this;
    }
}
