<?php

namespace App\Entity;


use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Repository\UserRepository;
use App\Entity\Expense;
use App\Entity\Event;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Ja existeix un usuari amb aquest email')]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'L\'email no pot estar buit')]
    #[Assert\Email(message: 'L\'email {{ value }} no és vàlid')]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Household $household = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'participants')]
    private Collection $events;

    /**
     * @var Collection<int, Expense>
     */
    #[ORM\OneToMany(targetEntity: Expense::class, mappedBy: 'paidBy')]
    private Collection $expensesPaid;

    /**
     * @var Collection<int, Expense>
     */
    #[ORM\ManyToMany(targetEntity: Expense::class, mappedBy: 'splitBetween')]
    private Collection $sharedExpenses;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'El nom no pot estar buit')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'El nom ha de tenir almenys {{ limit }} caràcters'
    )]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'El cognom no pot estar buit')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'El cognom ha de tenir almenys {{ limit }} caràcters'
    )]
    private ?string $lastName = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[+]?[0-9\s\-()]+$/',
        message: 'El número de telèfon no és vàlid'
    )]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: 'L\'URL de l\'avatar no és vàlida')]
    private ?string $avatar = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 500,
        maxMessage: 'La biografia no pot superar els {{ limit }} caràcters'
    )]
    private ?string $bio = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $joinedAt = null;

    #[ORM\Column]
    private ?bool $isActive = true;

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->expensesPaid = new ArrayCollection();
        $this->sharedExpenses = new ArrayCollection();
    }

    /**
     * Retorna el nom complet
     */
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
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
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getHousehold(): ?Household
    {
        return $this->household;
    }

    public function setHousehold(?Household $household): static
    {
        $this->household = $household;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->addParticipant($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            $event->removeParticipant($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Expense>
     */
    public function getExpensesPaid(): Collection
    {
        return $this->expensesPaid;
    }

    public function addExpensesPaid(Expense $expensesPaid): static
    {
        if (!$this->expensesPaid->contains($expensesPaid)) {
            $this->expensesPaid->add($expensesPaid);
            $expensesPaid->setPaidBy($this);
        }

        return $this;
    }

    public function removeExpensesPaid(Expense $expensesPaid): static
    {
        if ($this->expensesPaid->removeElement($expensesPaid)) {
            // set the owning side to null (unless already changed)
            if ($expensesPaid->getPaidBy() === $this) {
                $expensesPaid->setPaidBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Expense>
     */
    public function getSharedExpenses(): Collection
    {
        return $this->sharedExpenses;
    }

    public function addSharedExpense(Expense $sharedExpense): static
    {
        if (!$this->sharedExpenses->contains($sharedExpense)) {
            $this->sharedExpenses->add($sharedExpense);
            $sharedExpense->addSplitBetween($this);
        }

        return $this;
    }

    public function removeSharedExpense(Expense $sharedExpense): static
    {
        if ($this->sharedExpenses->removeElement($sharedExpense)) {
            $sharedExpense->removeSplitBetween($this);
        }

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function getJoinedAt(): ?\DateTime
    {
        return $this->joinedAt;
    }

    public function setJoinedAt(\DateTime $joinedAt): static
    {
        $this->joinedAt = $joinedAt;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }
}
