<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TagController extends Controller {
  public function indexAction($name) {
    return $this->render('', array('name' => $name));
  }
}
