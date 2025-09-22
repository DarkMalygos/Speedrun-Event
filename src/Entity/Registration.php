<?php

namespace App\Entity;

use App\Enum\RegistrationStatus;
use App\Repository\RegistrationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RegistrationRepository::class)]
#[ORM\Table(name: 'registration', uniqueConstraints: [new ORM\UniqueConstraint(name: 'registerOnce', columns: ['event_id', 'spectator_id'])])]
class Registration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'waitlist_position', type: 'integer', nullable: true)]
    private ?int $waitlistPosition = null;

    #[ORM\ManyToOne(inversedBy: 'registrations')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?RunEvent $event = null;

    #[ORM\ManyToOne(inversedBy: 'registrations')]
    #[ORM\JoinColumn(name: 'spectator_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Spectator $spectator = null;

    #[ORM\Column(enumType: RegistrationStatus::class)]
    private ?RegistrationStatus $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWaitlistPosition(): ?int
    {
        return $this->waitlistPosition;
    }

    public function setWaitlistPosition(?int $waitlistPosition): static
    {
        $this->waitlistPosition = $waitlistPosition;

        return $this;
    }

    public function getEvent(): ?RunEvent
    {
        return $this->event;
    }

    public function setEvent(?RunEvent $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getSpectator(): ?Spectator
    {
        return $this->spectator;
    }

    public function setSpectator(?Spectator $spectator): static
    {
        $this->spectator = $spectator;

        return $this;
    }

    public function getStatus(): ?RegistrationStatus
    {
        return $this->status;
    }

    public function setStatus(RegistrationStatus $status): static
    {
        $this->status = $status;

        return $this;
    }
}
