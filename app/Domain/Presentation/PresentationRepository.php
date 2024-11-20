<?php declare(strict_types = 1);

namespace App\Domain\Presentation;

use App\Domain\Reservation\Reservation;
use App\Domain\User\User;
use App\Domain\Room\Room;
use App\Model\Database\Repository\AbstractRepository;

/**
 * @method Presentation|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Presentation|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Presentation[] findAll()
 * @method Presentation[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Presentation>
 */
class PresentationRepository extends AbstractRepository
{
  public function findPresentationsBySpeaker($userId)
  {
    return $this->createQueryBuilder('r')
      ->where('r.speaker = :userId')
      ->setParameter('userId', $userId)
      ->orderBy('r.startsAt', 'DESC')
      ->getQuery()
      ->getResult();
  }

  public function findUpcomingPresentationsWithMostAttendances()
  {
    return $this->createQueryBuilder('p')
      ->leftJoin('p.attendances', 'a') // Spojí s tabulkou Attendance
      ->addSelect('COUNT(a.id) AS HIDDEN attendanceCount') // Spočítá počet účastníků
      ->where('p.endsAt > :currentDateTime') // Omezení na prezentace, které ještě neproběhly
      ->setParameter('currentDateTime', new \DateTime())
      ->groupBy('p.id') // Seskupení podle prezentace
      ->orderBy('attendanceCount', 'DESC') // Seřazení podle počtu účastníků
      ->setMaxResults(3) // Vrátí pouze 3 výsledky
      ->getQuery()
      ->getResult();
  }
  public function findCollisions(Room $room, \DateTimeImmutable $startsAt, \DateTimeImmutable $endsAt, ?int $excludePresentationId = null): array
  {
      $qb = $this->createQueryBuilder('p')
          ->where('p.room = :room')
          ->andWhere('(p.startsAt < :endsAt) AND (p.endsAt > :startsAt)')
          ->setParameter('room', $room)
          ->setParameter('startsAt', $startsAt)
          ->setParameter('endsAt', $endsAt);

      if ($excludePresentationId !== null) {
          $qb->andWhere('p.id != :excludeId')
              ->setParameter('excludeId', $excludePresentationId);
      }

      return $qb->getQuery()->getResult();
  }



}
