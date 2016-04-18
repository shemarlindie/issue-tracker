<?php

namespace AppBundle\Repository;

/**
 * IssueRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class IssueRepository extends FilteredEntityRepository {
  public function getFilterFields() {
    return array(
      'reportedById',
      'projectId',
      'statusId',
      'priorityId',
      'typeId'
    );
  }

  public function filter($params) {
    $qb = parent::filter($params);

    if (array_key_exists('assignedToId', $params)) {
      $value = $params['assignedToId'];

      if (is_numeric($value) || !empty($value)) {
        $qb->innerJoin('entity.fixers', 'u', 'WITH', 'u.id = :assignee')
          ->setParameter('assignee', $value);
      }
    }

    return $qb;
  }


}