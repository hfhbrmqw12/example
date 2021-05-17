<?php

namespace CRM\ContactBundle\Utils;


use Doctrine\ORM\EntityManager;

/**
 * Class ContactUtils
 */
class ContactUtils
{
    /** @var  EntityManager */
    protected $entityManager;

    /**
     * ContactUtils constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param $interval
     * @return array
     * @throws \Exception
     */
    public function getCongratulatedContacts($interval)
    {
        $later = (new \DateTime())->add(new \DateInterval('PT' . $interval . 'S'));
        $query = $this->entityManager->getRepository('CRMContactBundle:Contact')->createQueryBuilder('c')
            ->Where('MONTH(c.birthday) = :later_month and DAY(c.birthday) = :later_day and c.birthdayRemindBefore = :interval')
            ->setParameter('later_month', $later->format('m'))
            ->setParameter('later_day', $later->format('d'))
            ->setParameter('interval', $interval);

        return $query->getQuery()->getResult();
    }
}
