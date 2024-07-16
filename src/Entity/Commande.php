<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Attribute\Groups;


#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_SERVEUR')"),
        new GetCollection(security: "is_granted('ROLE_BARMAN')"),
        new Patch(security: "is_granted('ROLE_SERVEUR') or  && ( object.getStatus() != 'payée' or objet.getStatus() != 'prête' )"),
        new Delete(),
        new Post(security: "is_granted('ROLE_SERVEUR')")
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    forceEager: false,
)]

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('read')]
    
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdDate = null;

    /**
     * @var Collection<int, Boisson>
     */
    #[Groups(['read', 'write'])]
    #[ORM\ManyToMany(targetEntity: Boisson::class, inversedBy: 'commandes')]
    private Collection $BoissonCommandé;

    #[ORM\Column]
    #[Groups(['read', 'write'])]
    private ?int $TableNuméro = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read', 'write'])]
    private ?User $server = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read', 'write'])]
    private ?User $Barman = null;

    #[ORM\Column(length: 100)]
    #[Groups(['read', 'write'])]
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
