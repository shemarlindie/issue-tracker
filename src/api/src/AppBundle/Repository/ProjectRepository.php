<?php

namespace AppBundle\Repository;

/**
 * ProjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProjectRepository extends FilteredEntityRepository {
  public function search($text) {
    return $this->createQueryBuilder('project')
      ->where('project.name LIKE :text')
      ->orWhere('project.client LIKE :text')
      ->setParameter('text', '%'.$text.'%');
  }

  public function getFilterFields() {
    return array(
      'ownerId'
    );
  }
}
