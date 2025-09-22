<?php

namespace App\Entity;

use App\Repository\SpectatorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Unique;

#[ORM\Entity(repositoryClass: SpectatorRepository::class)]
#[ORM\Table(name: 'spectator')]
class Spectator
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'spectator_name', length: 100, nullable: false)]
    private ?string $spectatorName;
    
    #[ORM\Column(name: 'email', length: 100, nullable: false, unique: true)]
    private ?string $email = null;

    #[ORM\Column(name: 'password', length: 255, nullable: false)]
    private ?string $password = null;

    /**
     * @var Collection<int, Registration>
     */
    #[ORM\OneToMany(targetEntity: Registration::class, mappedBy: 'spectator', cascade: ['remove'])]
    private Collection $registrations;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSpectatorName(): ?string
    {
        return $this->spectatorName;
    }

    public function setSpectatorName(string $spectatorName): static
    {
        $this->spectatorName = $spectatorName;

        return $this;
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
     * @return Collection<int, Registration>
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function addRegistration(Registration $registration): static
    {
        if (!$this->registrations->contains($registration)) {
            $this->registrations->add($registration);
            $registration->setSpectator($this);
        }

        return $this;
    }

    public function removeRegistration(Registration $registration): static
    {
        if ($this->registrations->removeElement($registration)) {
            // set the owning side to null (unless already changed)
            if ($registration->getSpectator() === $this) {
                $registration->setSpectator(null);
            }
        }

        return $this;
    }
}
