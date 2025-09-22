<?php

namespace App\Entity;

use App\Repository\RunEventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RunEventRepository::class)]
#[ORM\Table(name: 'run_event')]
class RunEvent
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer', options: ['default' => 1])]
    private int $id = 1;

    #[ORM\Column(name: 'run_name', length: 70, nullable: false)]
    private ?string $run_name;

    #[ORM\Column(name: 'capacity', type: 'integer', nullable: false)]
    private ?int $capacity;

    /**
     * @var Collection<int, Registration>
     */
    #[ORM\OneToMany(targetEntity: Registration::class, mappedBy: 'event', cascade: ['remove'])]
    private Collection $registrations;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRunName(): ?string
    {
        return $this->run_name;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setRunName(string $run_name): static
    {
        $this->run_name = $run_name;

        return $this;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;

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
            $registration->setEvent($this);
        }

        return $this;
    }

    public function removeRegistration(Registration $registration): static
    {
        if ($this->registrations->removeElement($registration)) {
            // set the owning side to null (unless already changed)
            if ($registration->getEvent() === $this) {
                $registration->setEvent(null);
            }
        }

        return $this;
    }
}
