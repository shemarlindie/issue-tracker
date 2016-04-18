<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Issue;
use AppBundle\Entity\IssueComment;
use AppBundle\Util\ConstraintViolationSerializer;
use AppBundle\Util\QueryPager;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

class CommentController extends FOSRestController {
  /**
   * @Get("/comments")
   */
  public function indexAction(Request $request) {
    $filters = $request->query->all();
    $query = $this->getDoctrine()->getRepository('AppBundle:IssueComment')
      ->filter($filters)
      ->getQuery();

    $data = QueryPager::paginate($request, $query);
//    $data = $query->getResult();

    return $this->handleView($this->view($data));
  }

  /**
   * @Post("/comments")
   */
  public function createAction(Request $request) {
    return $this->updateAction($request, NULL);
  }

  /**
   * @Patch("/comments/{id}")
   */
  public function updateAction(Request $request, $id) {
    $em = $this->getDoctrine()->getManager();
    $validator = $this->get('validator');
    $user = $this->getUser();
    $data = $request->request->all();

    if ($id) {
      $comment = $em->getRepository('AppBundle:IssueComment')->find($id);

      if (!$comment) {
        throw $this->createNotFoundException("Comment:{$id} not found.");
      }
    }
    else {
      $comment = new IssueComment();
      $comment->setCommenter($user);

      if (array_key_exists('issue_id', $data)) {
        $issue = $em->getRepository('AppBundle:Issue')
          ->find($data['issue_id']);

        if ($issue) {
          $comment->setIssue($issue);
        }
      }

      if (array_key_exists('status_id', $data)) {
        $status = $em->getRepository('AppBundle:IssueStatus')
          ->find($data['status_id']);

        if ($status) {
          $comment->setStatus($status);
        }
      }
    }

    $comment->fill($data);

    $violations = $validator->validate($comment);
    if ($violations->count() > 0) {
      $errors = ConstraintViolationSerializer::serializeList($violations);

      return $this->handleView($this->view($errors, 400));
    }

    if (!$id) {
      $em->persist($comment);
    }
    $em->flush();

    return $this->handleView($this->view($comment));
  }

  /**
   * @Get("/comments/{id}")
   */
  public function detailAction($id) {
    $em = $this->getDoctrine()->getManager();
    $comment = $em->getRepository('AppBundle:IssueComment')->find($id);

    if (!$comment) {
      throw $this->createNotFoundException("Comment:{$id} not found.");
    }

    return $this->handleView($this->view($comment));
  }

  /**
   * @Delete("/comments/{id}")
   */
  public function deleteAction($id) {
    $em = $this->getDoctrine()->getManager();
    $comment = $em->getRepository('AppBundle:IssueComment')->find($id);

    if (!$comment) {
      throw $this->createNotFoundException("Comment:{$id} not found.");
    }

    $em->remove($comment);
    $em->flush();

    return $this->handleView($this->view($comment));
  }
}
