<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;

/**
 * MosqueRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MosqueRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param User $user
     * @param array $search
     * @return \Doctrine\ORM\QueryBuilder
     */
    function search(User $user, array $search)
    {
        $qb = $this->createQueryBuilder("m")
            ->leftJoin("m.user", "u", "m.user_id = u.id");

        if (!empty($search)) {
            if (!empty($search["word"])) {
                $qb->where("m.name LIKE :word "
                    . "OR m.associationName LIKE :word "
                    . "OR m.email LIKE :word "
                    . "OR m.address LIKE :word "
                    . "OR m.city LIKE :word "
                    . "OR m.zipcode LIKE :word "
                    . "OR m.country LIKE :word "
                    . "OR u.username LIKE :word "
                    . "OR u.email LIKE :word"
                )->setParameter(":word", "%" . $search["word"] . "%");
            }

            if (!empty($search["id"])) {
                $qb->andWhere("m.id = :id")
                    ->setParameter(":id", $search["id"]);
            }

            if (!empty($search["type"])) {
                $qb->andWhere("m.type = :type")
                    ->setParameter(":type", $search["type"]);
            }

            if (!empty($search["department"])) {
                $qb->andWhere("m.zipcode LIKE :zipcode")
                    ->setParameter(":zipcode", $search["department"] . "%");
            }
        }

        // By default not show homes for admin user
        if ($user->isAdmin() && empty($search["type"])) {
            $qb->andWhere("m.type = :type")
                ->setParameter(":type", "mosque");
        }

        if (!$user->isAdmin()) {
            $qb->andWhere("u.id = :userId")
                ->setParameter(":userId", $user->getId());
        }

        $qb->orderBy("m.created", "DESC");

        return $qb;
    }


    /**
     * @param string $search
     * @return \Doctrine\ORM\QueryBuilder
     */
    function publicSearch($search)
    {
        $qb = $this->createQueryBuilder("m");

        if (!empty($search)) {
            $qb->where("m.type = 'mosque'")
                ->andwhere("m.name LIKE :word "
                    . "OR m.associationName LIKE :word "
                    . "OR m.address LIKE :word "
                    . "OR m.city LIKE :word "
                    . "OR m.zipcode LIKE :word "
                    . "OR m.country LIKE :word "
                )->setParameter(":word", "%$search%");
        }

        return $qb;
    }


    /**
     * get configured mosques
     * @param integer $nbMax
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getMosquesQuery($nbMax = null)
    {
        $qb = $this->createQueryBuilder("m");
        if (is_numeric($nbMax)) {
            $qb->setMaxResults($nbMax);
        }
        return $qb;
    }

    /**
     * get configured mosques with minimum one image set (image1)
     * @param integer $nbMax
     * @return array
     */
    function getMosquesWithImage($nbMax = null)
    {
        return $this->getMosquesQuery($nbMax)
            ->where("m.image1 IS NOT NULL")
            ->andWhere("m.type = 'mosque'")
            ->orderBy("m.id", "DESC")
            ->getQuery()
            ->getResult();
    }

    /**
     * set updated to now for all mosques
     */
    function forceUpdateAll()
    {
        $qb = $this->createQueryBuilder("m")
            ->update()
            ->set("m.updated", ":date")
            ->setParameter(":date", new \DateTime());
        $qb->getQuery()->execute();
    }

    /**
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    function getCount()
    {
        return $this->createQueryBuilder("m")
            ->select("count(m.id)")
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    function countMosques()
    {
        return $this->createQueryBuilder("m")
            ->select("count(m.id)")
            ->where("m.type = 'mosque'")
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * get mosques information for google map
     * @return array
     */
    function getAllMosquesForMap()
    {
        return $this->createQueryBuilder("m")
            ->leftJoin("m.configuration", "c", "m.id = c.mosque_id")
            ->select("m.slug, m.name, m.address, m.city, m.zipcode, m.country,  c.longitude as lng, c.latitude as lat")
            ->where("m.addOnMap = 1")
            ->andWhere("m.type = 'mosque'")
            ->andWhere("c.latitude is not null")
            ->andWhere("c.longitude is not null")
            ->getQuery()
            ->getArrayResult();
    }


    /**
     * get mosques by country
     * @return array
     */
    function getNumberByCountry()
    {
        return $this->createQueryBuilder("m")
            ->select("count(m.id) as nb, m.country")
            ->orderBy("nb", "DESC")
            ->groupBy("m.country")
            ->getQuery()
            ->getResult();
    }
}
