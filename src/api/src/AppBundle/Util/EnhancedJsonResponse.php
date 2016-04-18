<?php
/**
 * Created by PhpStorm.
 * User: shemarcl
 * Date: 3/4/16
 * Time: 5:13 PM
 */

namespace AppBundle\Util;

class EnhancedJsonResponse extends \Symfony\Component\HttpFoundation\JsonResponse {
  private $serializer;

  public function __construct($data = null, $serializer = null, $status = 200, array $headers = array()) {
    $this->serializer = $serializer;
    parent::__construct($data, $status, $headers);
  }

  public function setData($data = array()) {
    if (isset($this->serializer)) {
      $this->data = $this->serializer->serialize($data, 'json');
      return parent::update();
    }

    return parent::setData($data);
  }
}