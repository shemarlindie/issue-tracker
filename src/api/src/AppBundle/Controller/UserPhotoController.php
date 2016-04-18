<?php

namespace AppBundle\Controller;

use AppBundle\Entity\UserPhoto;
use AppBundle\Util\ConstraintViolationSerializer;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class UserPhotoController extends FOSRestController {

  /**
   * @Post("/photos/user/{id}")
   */
  public function updateAction(Request $request, $id) {
    $em = $this->get('doctrine.orm.entity_manager');
    $validator = $this->get('validator');

    $user = $em->getRepository('AppBundle:User')
      ->find($id);

    if (!$user) {
      throw $this->createNotFoundException("User:{$id} not found.");
    }

    // remove previous photo if exists
    $oldPhoto = $user->getPhoto();
    if ($oldPhoto) {
      $user->setPhoto(NULL);
      $em->remove($oldPhoto);
      $em->flush();
    }

    $photo = NULL;

    /** @var UploadedFile $file */
    foreach ($request->files->all() as $file) {
      // skip files with an upload error
      if ($file->getError() !== UPLOAD_ERR_OK) {
        continue;
      }

      $photo = new UserPhoto($user, $file);
      $violations = $validator->validate($photo);
      if ($violations->count() > 0) {
        $errors = ConstraintViolationSerializer::serializeList($violations);

        return $this->handleView($this->view($errors, 400));
      }
      else {
        $photo->setUser($user);
        $em->persist($photo);
      }
    }

    $em->flush();

    return $this->handleView($this->view($photo));
  }

  /**
   * @Delete("/photos/user/{id}")
   */
  public function deleteAction($id) {
    $em = $this->get('doctrine.orm.entity_manager');

    $user = $em->getRepository('AppBundle:User')
      ->find($id);

    if (!$user) {
      throw $this->createNotFoundException("User:{$id} not found.");
    }

    $photo = $user->getPhoto();

    if ($photo) {
      $user->setPhoto(NULL);
      $em->remove($photo);
      $em->flush();
    }

    return $this->handleView($this->view($photo));
  }
}
