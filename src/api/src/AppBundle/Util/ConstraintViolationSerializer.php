<?php
/**
 * Created by PhpStorm.
 * User: shemarcl
 * Date: 3/2/16
 * Time: 8:04 AM
 */

namespace AppBundle\Util;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ConstraintViolationSerializer {
  /**
   * @param ConstraintViolationInterface $violation
   * @return array
   */
  static function serialize(ConstraintViolationInterface $violation) {
    $serial = array();

    $serial[$violation->getPropertyPath()] = $violation->getMessage();

    return $serial;
  }

  /**
   * @param ConstraintViolationListInterface $violationList
   * @return array
   */
  static function serializeList(ConstraintViolationListInterface $violationList) {
    $serial = array();

    /** @var ConstraintViolationInterface $violation */
    foreach ($violationList as $violation) {
      $serial[$violation->getPropertyPath()][] = $violation->getMessage();
    }

    return $serial;
  }
}