<?php

namespace App\Services;

use App\Entity\FoodRecord;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use DateTime;

class Diary
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getDailyRemainingCalories($user, DateTime $date): ?int
    {
        return User::MAX_ADVICED_DAILY_CALORIES - $this->getTotalCalories($user, $date);
    }

    public function getTotalCalories($user, DateTime $date): ?int
    {
        if (!$user) {
            return null;
        }

        $foodRecords = $this->em->getRepository(FoodRecord::class)->findBy(
            [
                'userId' => $user->getId(),
                'recordedAt' => $date
            ]
        );

        $totalCalories = 0;

        foreach ($foodRecords as $foodRecord) {
            $totalCalories += $foodRecord->getCalories();
        }

        return $totalCalories;
    }
}