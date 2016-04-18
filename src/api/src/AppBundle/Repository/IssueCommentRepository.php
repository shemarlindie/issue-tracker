<?php

namespace AppBundle\Repository;

/**
 * IssueCommentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class IssueCommentRepository extends FilteredEntityRepository {
  public function getFilterFields() {
    return array(
      'commenterId',
      'issueId',
      'statusId'
    );
  }
}