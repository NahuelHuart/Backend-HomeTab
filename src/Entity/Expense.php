<?php

namespace App\Entity;

use App\Repository\ExpenseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExpenseRepository::class)]
class Expense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'El títol de la despesa no pot estar buit')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'El títol ha de tenir almenys {{ limit }} caràcters'
    )]
    private ?string $title = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La descripció no pot estar buida')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'La descripció ha de tenir almenys {{ limit }} caràcters'
    )]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotNull(message: 'L\'import és obligatori')]
    #[Assert\Positive(message: 'L\'import ha de ser positiu')]
    #[Assert\Range(
        min: 0.01,
        max: 99999.99,
        notInRangeMessage: 'L\'import ha d\'estar entre {{ min }}€ i {{ max }}€'
    )]
    private ?string $amount = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Choice(
        choices: ['Lloger', 'Factures', 'Menjar', 'Neteja', 'Transport', 'Altres'],
        message: 'Tria una categoria vàlida'
    )]
    private ?string $category = null;

    #[ORM\ManyToOne(inversedBy: 'expensesPaid')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $paidBy = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: 'La data de pagament és obligatòria')]
    #[Assert\LessThanOrEqual(
        'today',
        message: 'La data de pagament no pot ser futura'
    )]
    private ?\DateTimeInterface $paidAt = null;

    #[ORM\ManyToOne(inversedBy: 'expenses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Household $household = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'sharedExpenses')]
    #[Assert\Count(
        min: 1,
        minMessage: 'Has de seleccionar almenys un participant'
    )]
    private Collection $splitBetween;

    #[ORM\Column]
    private ?bool $isPaid = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    public function __construct()
    {
        $this->splitBetween = new ArrayCollection();
        $this->isPaid = false;
    }

    /**
     * Calcula quant ha de pagar cada persona
     */
    public function getAmountPerPerson(): float
    {
        $totalPeople = $this->splitBetween->count();
        if ($totalPeople === 0) {
            return 0;
        }
        return (float)$this->amount / $totalPeople;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getPaidBy(): ?User
    {
        return $this->paidBy;
    }

    public function setPaidBy(?User $paidBy): static
    {
        $this->paidBy = $paidBy;

        return $this;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(\DateTime $paidAt): static
    {
        $this->paidAt = $paidAt;

        return $this;
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
     * @return Collection<int, User>
     */
    public function getSplitBetween(): Collection
    {
        return $this->splitBetween;
    }

    public function addSplitBetween(User $splitBetween): static
    {
        if (!$this->splitBetween->contains($splitBetween)) {
            $this->splitBetween->add($splitBetween);
        }

        return $this;
    }

    public function removeSplitBetween(User $splitBetween): static
    {
        $this->splitBetween->removeElement($splitBetween);

        return $this;
    }

    public function isPaid(): ?bool
    {
        return $this->isPaid;
    }

    public function setIsPaid(bool $isPaid): static
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }
}
