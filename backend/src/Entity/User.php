<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use App\Entity\ShoppingList;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['list:read', 'item:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['list:read', 'item:read'])]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: ShoppingList::class, orphanRemoval: true)]
    private Collection $shopping_lists;

    /**
     * @var Collection<int, ShoppingList>
     */
    // 💡 Kétoldalú kapcsolat: ez a lista tartalmazza azokat a listákat,
    // amikhez a felhasználó hozzá lett adva tagként.
    #[ORM\ManyToMany(targetEntity: ShoppingList::class, mappedBy: 'members')]
    private Collection $sharedShoppingLists;

    public function __construct()
    {
        $this->shopping_lists = new ArrayCollection();
        $this->sharedShoppingLists = new ArrayCollection();
    }

    /**
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // Ha valamilyen ideiglenes, nem tartós adatot (pl. plain jelszót)
        // tároltunk, azt itt törölhetjük.
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return Collection<int, ShoppingList>
     */
    public function getShoppingLists(): Collection
    {
        return $this->shopping_lists;
    }

    /**
     * @return Collection<int, ShoppingList>
     */
    public function getSharedShoppingLists(): Collection
    {
        return $this->sharedShoppingLists;
    }

    public function addSharedShoppingList(ShoppingList $sharedShoppingList): static
    {
        if (!$this->sharedShoppingLists->contains($sharedShoppingList)) {
            $this->sharedShoppingLists->add($sharedShoppingList);
            $sharedShoppingList->addMember($this);
        }

        return $this;
    }

    public function removeSharedShoppingList(ShoppingList $sharedShoppingList): static
    {
        if ($this->sharedShoppingLists->removeElement($sharedShoppingList)) {
            $sharedShoppingList->removeMember($this);
        }

        return $this;
    }
}
