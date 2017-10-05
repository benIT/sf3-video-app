<?php

namespace AppBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\Value;
use Doctrine\ORM\QueryBuilder;

/**
 * VideoRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 *
 */
class VideoRepository extends \Doctrine\ORM\EntityRepository
{
    public function findLatest($nth)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT v FROM AppBundle:Video v ORDER BY v.id DESC'
            )
            ->setMaxResults($nth)->getResult();
    }


    /**
     * This method performs search.
     * A queryBuilder is first created and then filters are dynamical applied to this queryBuilder.
     * @param $criteria
     * @return array
     */
    public function findVideo($criteria)
    {
        $qb = $this->createQueryBuilder('video');
        $criteria = array_filter($criteria); //remove fields with no value
        foreach ($criteria as $field => $value) {
            if ($value instanceof ArrayCollection) {
                if ($value->isEmpty()) {
                    continue;
                }
            }
            $addFilterMethod = sprintf('%s%s', 'addFilter', ucfirst($field)); //build the dynamic filter to apply on $qb
            $qb = $this->$addFilterMethod($qb, $criteria[$field]);
        }
        $qb->orderBy('video.id', 'DESC');
        return $qb->getQuery()->getResult();
    }

    /**
     * add filter on title field
     * @param QueryBuilder $qb
     * @param string $title
     * @return QueryBuilder
     */
    private function addFilterTitle(QueryBuilder $qb, $title)
    {
        $criteria = Criteria::create()->andWhere(new Comparison('title', Comparison::CONTAINS, $title));
        $qb->addCriteria($criteria);
        return $qb;
    }

    /**
     * add filter on tags field
     * @param QueryBuilder $qb
     * @param ArrayCollection $tags
     * @return QueryBuilder
     */
    private function addFilterTags(QueryBuilder $qb, ArrayCollection $tags)
    {
        $qb->innerJoin('video.tags', 'tags');
        $criteria = Criteria::create()->andWhere(new Comparison('tags', Comparison::IN, new Value($tags)));
        $qb->addCriteria($criteria);
        return $qb;
    }

    private function addFilterDescription(QueryBuilder $qb, $description)
    {
        $criteria = Criteria::create()->andWhere(new Comparison('description', Comparison::CONTAINS, $description));
        $qb->addCriteria($criteria);
        return $qb;
    }

}
