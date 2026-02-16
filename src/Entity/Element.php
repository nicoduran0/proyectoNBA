<?php

namespace App\Entity;

use App\Repository\ElementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ElementRepository::class)]
class Element
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stats = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $number = null;

    #[ORM\Column(nullable: true)]
    private ?int $apiId = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $team = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $position = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $image = null;

    // --- CAMBIO REALIZADO: AHORA ES MANY-TO-MANY ---
    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'elements')]
    private Collection $categories;
    // -----------------------------------------------

    /**
     * @var Collection<int, Rating>
     */
    #[ORM\OneToMany(targetEntity: Rating::class, mappedBy: 'element', orphanRemoval: true)]
    private Collection $ratings;

    /**
     * @var Collection<int, Ranking>
     */
    #[ORM\ManyToMany(targetEntity: Ranking::class, mappedBy: 'elements')]
    private Collection $rankings;

    public function __construct()
    {
        $this->ratings = new ArrayCollection();
        $this->rankings = new ArrayCollection();
        // IMPORTANTE: Inicializar la colección de categorías
        $this->categories = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStats(): ?string
    {
        return $this->stats;
    }

    public function setStats(?string $stats): static
    {
        $this->stats = $stats;

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

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getApiId(): ?int
    {
        return $this->apiId;
    }

    public function setApiId(?int $apiId): static
    {
        $this->apiId = $apiId;

        return $this;
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

    public function getTeam(): ?string
    {
        return $this->team;
    }

    public function setTeam(?string $team): static
    {
        $this->team = $team;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    // --- NUEVOS MÉTODOS PARA CATEGORÍAS (ADD/REMOVE) ---

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }
    // ---------------------------------------------------

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): static
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings->add($rating);
            $rating->setElement($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): static
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getElement() === $this) {
                $rating->setElement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Ranking>
     */
    public function getRankings(): Collection
    {
        return $this->rankings;
    }

    public function addRanking(Ranking $ranking): static
    {
        if (!$this->rankings->contains($ranking)) {
            $this->rankings->add($ranking);
            $ranking->addElement($this);
        }

        return $this;
    }

    public function removeRanking(Ranking $ranking): static
    {
        if ($this->rankings->removeElement($ranking)) {
            $ranking->removeElement($this);
        }

        return $this;
    }

    public function getCalculatedAverage(): float
    {
        $ratings = $this->getRatings();

        if ($ratings->isEmpty()) {
            return 0.0;
        }

        $total = 0;
        foreach ($ratings as $rating) {
            $total += $rating->getScore();
        }

        return $total / $ratings->count();
    }
}
