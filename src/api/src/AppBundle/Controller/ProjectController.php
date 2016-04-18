<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Project;
use AppBundle\Util\ConstraintViolationSerializer;
use AppBundle\Util\QueryPager;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

class ProjectController extends FOSRestController {

  /**
   * @Get("/projects")
   */
  public function indexAction(Request $request) {
    $filters = $request->query->all();
    $query = $this->getDoctrine()->getRepository('AppBundle:Project')
      ->filter($filters)
      ->getQuery();

    $data = QueryPager::paginate($request, $query);
//    $data = $query->getResult();

    return $this->handleView($this->view($data));
  }

  /**
   * @Get("/projects/search")
   */
  public function searchAction(Request $request) {
    $text = $request->query->get('query');

    $query = $this->getDoctrine()->getRepository('AppBundle:Project')
      ->search($text)
      ->getQuery();

    $data = QueryPager::paginate($request, $query);
//    $data = $query->getResult();

    return $this->handleView($this->view($data));
  }

  /**
   * @Post("/projects")
   */
  public function createAction(Request $request) {
    return $this->updateAction($request, NULL);
  }

  /**
   * @Patch("/projects/{id}")
   */
  public function updateAction(Request $request, $id) {
    $em = $this->getDoctrine()->getManager();
    $validator = $this->get('validator');
    $user = $this->getUser();

    if ($id) {
      $project = $em->getRepository('AppBundle:Project')->find($id);

      if (!$project) {
        throw $this->createNotFoundException("Project:{$id} not found.");
      }
    }
    else {
      $project = new Project();
      $project->setOwner($user);
    }

    $data = $request->request->all();
    $project->fill($data);

    if (array_key_exists('collaborators', $data)) {
      $collaborators = new ArrayCollection();

      foreach ($data['collaborators'] as $userId) {
        $user = $em->getRepository('AppBundle:User')->find($userId);

        if ($user) {
          $collaborators->add($user);
        }
      }

      $project->setCollaborators($collaborators);
    }

    if (array_key_exists('date_due', $data)) {
      $project->setDateDue(NULL);

      $dateStr = $data['date_due'];
      if (strtotime($dateStr)) {
        $project->setDateDue(new \DateTime($dateStr));
      }
    }

    $violations = $validator->validate($project);
    if ($violations->count() > 0) {
      $errors = ConstraintViolationSerializer::serializeList($violations);

      return $this->handleView($this->view($errors, 400));
    }

    if (!$id) {
      $em->persist($project);
    }
    $em->flush();

    return $this->handleView($this->view($project));
  }

  /**
   * @Get("/projects/{id}")
   */
  public function detailAction($id) {
    $em = $this->getDoctrine()->getManager();
    $project = $em->getRepository('AppBundle:Project')->find($id);

    if (!$project) {
      throw $this->createNotFoundException("Project:{$id} not found.");
    }

    return $this->handleView($this->view($project));
  }

  /**
   * @Delete("/projects/{id}")
   */
  public function deleteAction($id) {
    $em = $this->getDoctrine()->getManager();
    $project = $em->getRepository('AppBundle:Project')->find($id);

    if (!$project) {
      throw $this->createNotFoundException("Project:{$id} not found.");
    }

    $em->remove($project);
    $em->flush();

    return $this->handleView($this->view($project));
  }
}
