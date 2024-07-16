<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Metadata\ApiResource;


#[ApiResource()]
#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdDate = null;

    /**
     * @var Collection<int, Boisson>
     */
    #[ORM\ManyToMany(targetEntity: Boisson::class, inversedBy: 'commandes')]
    private Collection $BoissonCommandé;

    #[ORM\Column]
    private ?int $TableNuméro = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $server = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $Barman = null;

    #[ORM\Column(length: 100)]
    private ?string $status = null;

    public function __construct()
    {
        $this->BoissonCommandé = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): static
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * @return Collection<int, Boisson>
     */
    public function getBoissonCommandé(): Collection
    {
        return $this->BoissonCommandé;
    }

    public function addBoissonCommand(Boisson $boissonCommand): static
    {
        if (!$this->BoissonCommandé->contains($boissonCommand)) {
            $this->BoissonCommandé->add($boissonCommand);
        }

        return $this;
    }

    public function removeBoissonCommand(Boisson $boissonCommand): static
    {
        $this->BoissonCommandé->removeElement($boissonCommand);

        return $this;
    }

    public function getTableNuméro(): ?int
    {
        return $this->TableNuméro;
    }

    public function setTableNuméro(int $TableNuméro): static
    {
        $this->TableNuméro = $TableNuméro;

        return $this;
    }

    public function getServer(): ?User
    {
        return $this->server;
    }

    public function setServer(?User $server): static
    {
        $this->server = $server;

        return $this;
    }

    public function getBarman(): ?User
    {
        return $this->Barman;
    }

    public function setBarman(?User $Barman): static
    {
        $this->Barman = $Barman;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
