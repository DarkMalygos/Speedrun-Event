<?php 
namespace App\Service;

use App\Entity\Registration;
use App\Entity\RunEvent;
use App\Entity\Spectator;
use App\Enum\RegistrationStatus;
use App\Repository\RegistrationRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class RegistrationService {
    public function __construct(private EntityManagerInterface $emi, private RegistrationRepository $registrationRepository) {}

    public function addOrGetExisting(Spectator $spectator, RunEvent $runEvent) {
        $existing = $this->registrationRepository->findOneBy([
            'spectator' => $spectator, 'event' => $runEvent
        ]);

        if($existing) {
            return $existing;
        }

        $registration = new Registration();
        $registration->setSpectator($spectator);
        $registration->setEvent($runEvent);

        $standbyCount = $this->registrationRepository->countMembers($runEvent);
        $capacity = (int) $runEvent->getCapacity();

        if ($standbyCount < $capacity) {
            $registration->setStatus(RegistrationStatus::STANDBY);
            $registration->setWaitlistPosition(null);
        } 
        
        else {
            $registration->setStatus(RegistrationStatus::WAITLIST);
            $registration->setWaitlistPosition($this->registrationRepository->getWaitlistPosition($runEvent));
        }

        $this->emi->persist($registration);

        try {
            $this->emi->flush();
        } 
        
        catch (UniqueConstraintViolationException $error) {
            $existing = $this->registrationRepository->findOneBy([
            'spectator' => $spectator, 'event' => $runEvent]);

            if($existing) {
                return $existing;
            }
            throw $error;
        }
        return $registration;
    }

    public function deleteAndPromoteOneMember(Registration $registration): void {
        $runEvent = $registration->getEvent();
        $wasStandby = $registration->getStatus() === RegistrationStatus::STANDBY;
        $oldWaitlistPosition = $registration->getWaitlistPosition();

        $this->emi->remove($registration);
        $this->emi->flush();

        if ($wasStandby) {
            $firstWaitlistPosition = $this->registrationRepository->findFirstOnWaitList($runEvent);
            if ($firstWaitlistPosition) {
                $firstWaitlistPosition->setStatus(RegistrationStatus::STANDBY);
                $firstWaitlistPosition->setWaitlistPosition(null);
                $this->emi->flush();
            }
        }

        elseif ($oldWaitlistPosition !== null) {
            $this->registrationRepository->decrementWaitlistPosition($runEvent, $oldWaitlistPosition);
        }
    }
}
?>