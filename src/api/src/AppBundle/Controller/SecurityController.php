<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\FOSRestController;

class SecurityController extends FOSRestController {
  /**
   * @Post("/auth/auth_check")
   */
  public function authCheckAction() {
    $user = $this->getUser();

    return $this->handleView($this->view($user));
  }
}
