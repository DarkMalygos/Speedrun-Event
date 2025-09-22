<?php
namespace App\Repository;

use App\Entity\Registration;
use App\Enum\RegistrationStatus;
use App\Entity\RunEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Registration>
 */
class RegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Registration::class);
    }

    public function countMembers(RunEvent $runEvent): int
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.event = :event')
            ->andWhere('r.status = :status')
            ->setParameter('event', $runEvent)
            ->setParameter('status', RegistrationStatus::STANDBY)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getWaitlistPosition(RunEvent $runEvent): int
    {
        $max = $this->createQueryBuilder('r')
            ->select('MAX(r.waitlistPosition)')
            ->andWhere('r.event = :event')
            ->andWhere('r.status = :status')
            ->setParameter('event', $runEvent)
            ->setParameter('status', RegistrationStatus::WAITLIST)
            ->getQuery()
            ->getSingleScalarResult();
        
        return $max ? $max + 1 : 1;
    }

    public function findFirstOnWaitList(RunEvent $runEvent): ?Registration {
        return $this->findOneBy(
            ['event' => $runEvent, 'status' => RegistrationStatus::WAITLIST], ['waitlistPosition' => 'ASC']
        );
    }

    public function decrementWaitlistPosition(RunEvent $runEvent, int $decrementFrom) {
        $query = $this->createQueryBuilder('r')
            ->update()
            ->set('r.waitlistPosition', 'r.waitlistPosition - 1')
            ->andWhere('r.event = :event')
            ->andWhere('r.status = :status')
            ->andWhere('r.waitlistPosition > :from')
            ->setParameter('event', $runEvent)
            ->setParameter('status', RegistrationStatus::WAITLIST)
            ->setParameter('from', $decrementFrom);
        
            $query->getQuery()->execute();
    }

    //    /**
    //     * @return Registration[] Returns an array of Registration objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Registration
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
