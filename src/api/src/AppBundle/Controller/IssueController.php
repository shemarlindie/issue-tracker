<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Issue;
use AppBundle\Util\ConstraintViolationSerializer;
use AppBundle\Util\QueryPager;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

class IssueController extends FOSRestController {
  /**
   * @Get("/issues")
   */
  public function indexAction(Request $request) {
    $filters = $request->query->all();
    $query = $this->getDoctrine()->getRepository('AppBundle:Issue')
      ->filter($filters)
      ->getQuery();

    $data = QueryPager::paginate($request, $query);
//    $data = $query->getResult();

    return $this->handleView($this->view($data));
  }

  /**
   * @Post("/issues")
   */
  public function createAction(Request $request) {
    return $this->updateAction($request, NULL);
  }

  /**
   * @Patch("/issues/{id}", requirements={"id"="\d+"})
   */
  public function updateAction(Request $request, $id) {
    $em = $this->getDoctrine()->getManager();
    $validator = $this->get('validator');
    $user = $this->getUser();

    if ($id) {
      $issue = $em->getRepository('AppBundle:Issue')->find($id);

      if (!$issue) {
        throw $this->createNotFoundException("Issue:{$id} not found.");
      }
    }
    else {
      $issue = new Issue();
      $issue->setReportedBy($user);
    }

    $data = $request->request->all();
    $issue->fill($data);

    if (array_key_exists('project_id', $data)) {
      $project = $em->getRepository('AppBundle:Project')->find($data['project_id']);

      if ($project) {
        $issue->setProject($project);
      }
    }

    if (array_key_exists('status_id', $data)) {
      $status = $em->getRepository('AppBundle:IssueStatus')->find($data['status_id']);

      if ($status) {
        $issue->setStatus($status);
      }
    }

    if (array_key_exists('type_id', $data)) {
      $type = $em->getRepository('AppBundle:IssueType')->find($data['type_id']);

      if ($type) {
        $issue->setType($type);
      }
    }

    if (array_key_exists('priority_id', $data)) {
      $priority = $em->getRepository('AppBundle:IssuePriority')->find($data['priority_id']);

      if ($priority) {
        $issue->setPriority($priority);
      }
    }

    if (array_key_exists('testers', $data)) {
      $testers = new ArrayCollection();
      foreach ($data['testers'] as $userId) {
        $user = $em->getRepository('AppBundle:User')->find($userId);

        if ($user) {
          $testers->add($user);
        }
      }
      $issue->setTesters($testers);
    }

    if (array_key_exists('fixers', $data)) {
      $fixers = new ArrayCollection();

      foreach ($data['fixers'] as $userId) {
        $user = $em->getRepository('AppBundle:User')->find($userId);

        if ($user) {
          $fixers->add($user);
        }
      }

      $issue->setFixers($fixers);
    }

    if (array_key_exists('date_due', $data)) {
      $issue->setDateDue(NULL);

      $dateStr = $data['date_due'];
      if (strtotime($dateStr)) {
        $issue->setDateDue(new \DateTime($dateStr));
      }
    }

    $violations = $validator->validate($issue);
    if ($violations->count() > 0) {
      $errors = ConstraintViolationSerializer::serializeList($violations);

      return $this->handleView($this->view($errors, 400));
    }

    if (!$id) {
      $em->persist($issue);
    }
    $em->flush();

    return $this->handleView($this->view($issue));
  }

  /**
   * @Get("/issues/{id}", requirements={"id"="\d+"})
   */
  public function detailAction($id) {
    $em = $this->getDoctrine()->getManager();
    $issue = $em->getRepository('AppBundle:Issue')->find($id);

    if (!$issue) {
      throw $this->createNotFoundException("Issue:{$id} not found.");
    }

    return $this->handleView($this->view($issue));
  }

  /**
   * @Delete("/issues/{id}", requirements={"id"="\d+"})
   */
  public function deleteAction($id) {
    $em = $this->getDoctrine()->getManager();
    $issue = $em->getRepository('AppBundle:Issue')->find($id);

    if (!$issue) {
      throw $this->createNotFoundException("Issue:{$id} not found.");
    }

    $em->remove($issue);
    $em->flush();

    return $this->handleView($this->view($issue));
  }

  /**
   * @Get("/issues/types")
   */
  public function indexTypes() {
    $em = $this->getDoctrine()->getManager();

    $data = $em->getRepository('AppBundle:IssueType')
      ->findAll();

    return $this->handleView($this->view($data));
  }

  /**
   * @Get("/issues/statuses")
   */
  public function indexStatuses() {
    $em = $this->getDoctrine()->getManager();

    $data = $em->getRepository('AppBundle:IssueStatus')
      ->findAll();

    return $this->handleView($this->view($data));
  }

  /**
   * @Get("/issues/priorities")
   */
  public function indexPriorities() {
    $em = $this->getDoctrine()->getManager();

    $data = $em->getRepository('AppBundle:IssuePriority')
      ->findAll();

    return $this->handleView($this->view($data));
  }
}
