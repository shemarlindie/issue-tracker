<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Util\ConstraintViolationSerializer;
use AppBundle\Util\Mailer;
use AppBundle\Util\QueryPager;
use AppBundle\Util\TokenGenerator;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class UserController extends FOSRestController {

  /**
   * @Get("/users")
   */
  public function indexAction(Request $request) {
    $query = $this->getDoctrine()->getRepository('AppBundle:User')
      ->createQueryBuilder('u')
      ->getQuery();

    $data = QueryPager::paginate($request, $query);

    return $this->handleView($this->view($data));
  }

  /**
   * @Get("/users/search")
   */
  public function searchAction(Request $request) {
    $text = $request->query->get('query');

    $query = $this->getDoctrine()->getRepository('AppBundle:User')
      ->search($text)
      ->getQuery();

    $data = QueryPager::paginate($request, $query);

    return $this->handleView($this->view($data));
  }

  /**
   * @Get("/users/{id}", requirements={"id":"\d+"})
   */
  public function detailAction($id) {
    $user = $this->getDoctrine()->getManager()
      ->getRepository('AppBundle:User')
      ->find($id);

    if (!$user) {
      throw $this->createNotFoundException("User:{$id} not found.");
    }

    return $this->handleView($this->view($user));
  }

  /**
   * @Patch("/users/{id}", requirements={"id":"\d+"})
   */
  public function updateAction(Request $request, $id) {
    /** @var User $user */
    $user = $this->getDoctrine()->getManager()
      ->getRepository('AppBundle:User')
      ->find($id);

    if (!$user) {
      throw $this->createNotFoundException("User:{$id} not found.");
    }

    /** @var ValidatorInterface $validator */
    $validator = $this->get('validator');
    $em = $this->getDoctrine()->getManager();

    $data = $request->request->all();
    $user
      ->fill($data)
      ->updateCanonicalFields();

    $violations = $validator->validate($user, NULL, array('profile'));

    if ($violations->count() > 0) {
      $errors = ConstraintViolationSerializer::serializeList($violations);

      return $this->handleView($this->view($errors, 400));
    }

    $em->flush();

    $token = $this->get('lexik_jwt_authentication.jwt_manager')->create($user);
    $user->setToken($token);

    return $this->handleView($this->view($user));
  }

  /**
   * @Delete("/users/{id}")
   */
  public function deleteAction($id) {
    /** @var User $user */
    $user = $this->getDoctrine()->getManager()
      ->getRepository('AppBundle:User')
      ->find($id);

    if (!$user) {
      throw $this->createNotFoundException("User:{$id} not found.");
    }

    $em = $this->getDoctrine()->getManager();
    $em->remove($user);
    $em->flush();

    return $this->handleView($this->view($user));
  }

  /**
   * @Post("/users")
   */
  public function createAction(Request $request) {
    /** @var ValidatorInterface $validator */
    $validator = $this->get('validator');
    $encoder = $this->get('security.password_encoder');
    $em = $this->getDoctrine()->getManager();

    /** @var User $user */
    $user = new User();
    $user->setEnabled(true); // todo: remove this when email integration is setup
    $data = $request->request->all();

    $user
      ->fill($data)
      ->updateCanonicalFields();

    if (isset($data['plain_password'])) {
      $user->setPlainPassword($data['plain_password']);

      if (empty($data['plain_password_confirmation']) || ($data['plain_password_confirmation'] !== $data['plain_password'])
      ) {
        $pwViolation = new ConstraintViolation('Your password and password confirmation do no match.',
          NULL, array(), NULL, 'plain_password_confirmation', NULL);
      }
    }

    $violations = $validator->validate($user, NULL, array('registration'));
    if (!empty($pwViolation)) {
      $violations->add($pwViolation);
    }

    if ($violations->count() > 0) {
      $errors = ConstraintViolationSerializer::serializeList($violations);

      return $this->handleView($this->view($errors, 400));
    }

    $user->setPassword($encoder->encodePassword($user, $user->getPlainPassword()));
    $user->eraseCredentials();

    // set confirmation token before user is saved
    $sendActivationEmail = FALSE;
    if (!$user->isEnabled()) {
      $token = (new TokenGenerator())->generateToken();
      $user->setConfirmationToken($token);
      $sendActivationEmail = TRUE;
    }

    // save user
    $em->persist($user);
    $em->flush();

    // send email only after user is saved
    if ($sendActivationEmail) {
      $this->get('app.mailer')->sendAccountActivationMessage($user);
    }

    // send login token if user is enabled
    if ($user->isEnabled()) {
      $token = $this->get('lexik_jwt_authentication.jwt_manager')
        ->create($user);
      $user->setToken($token);
    }

    return $this->handleView($this->view($user));
  }

  /**
   * @Post("/users/profile")
   */
  public function updateProfileAction(Request $request) {
    /** @var User $user */
    $user = $this->getUser();
    $encoder = $this->get('security.password_encoder');

    $currentPassword = $request->request->get('current_password');

    if (!$encoder->isPasswordValid($user, $currentPassword)) {
      $violations = new ConstraintViolationList();
      $violations->add(new ConstraintViolation('Incorrect password.', NULL,
        array(), NULL, 'current_password', NULL));

      $errors = ConstraintViolationSerializer::serializeList($violations);

      return $this->handleView($this->view($errors, 400));
    }

    return $this->updateAction($request, $user->getId());
  }

  /**
   * @Post("/users/{id}/change-password", requirements={"id"="\d+"})
   */
  public function changePasswordAction(Request $request, $id) {
    $encoder = $this->get('security.password_encoder');
    $em = $this->getDoctrine()->getManager();
    $validator = $this->get('validator');

    $user = $em->getRepository('AppBundle:User')
      ->find($id);

    if (!$user) {
      throw $this->createNotFoundException("User:{$id} not found.");
    }

    $data = $request->request->all();

    if (isset($data['plain_password'])) {
      $user->setPlainPassword($data['plain_password']);

      if (empty($data['plain_password_confirmation']) || ($data['plain_password_confirmation'] !== $data['plain_password'])
      ) {
        $pwViolation = new ConstraintViolation('Your password and password confirmation do no match.', NULL, array(), NULL, 'plain_password_confirmation', NULL);
      }
    }

    if (isset($data['current_password'])) {
      if (!$encoder->isPasswordValid($user, $data['current_password'])) {
        $currentPwViolation = new ConstraintViolation('Your current password is invalid.', NULL, array(), NULL, 'current_password', NULL);
      }
    }
    else {
      $currentPwViolation = new ConstraintViolation('Your current password is required.', NULL, array(), NULL, 'current_password', NULL);
    }

    $violations = $validator->validate($user, NULL, array('change_password'));
    if (!empty($pwViolation)) {
      $violations->add($pwViolation);
    }
    if (!empty($currentPwViolation)) {
      $violations->add($currentPwViolation);
    }

    if ($violations->count() > 0) {
      $errors = ConstraintViolationSerializer::serializeList($violations);

      return $this->handleView($this->view($errors, 400));
    }

    $user->setPassword($encoder->encodePassword($user, $user->getPlainPassword()));
    $user->eraseCredentials();

    $em->flush();

    return $this->handleView($this->view($user));
  }

  /**
   * @Post("/users/activate-account")
   */
  public function activateAccountAction(Request $request) {
    $em = $this->getDoctrine()->getManager();

    $data = $request->request->all();
    $errors = array();

    if (empty($data['token'])) {
      $errors['form'] = 'Invalid token.';
      return $this->handleView($this->view($errors, 400));
    }

    /** @var User $user */
    $user = $em->getRepository('AppBundle:User')
      ->findByConfirmationToken($data['token']);

    if (!$user) {
      $errors['form'] = "Token '{$data['token']}' not found.";
      return $this->handleView($this->view($errors, 400));
    }

    if ($user->isEnabled()) {
      $errors['form'] = "User account '{$user->getUsername()}' is already activated.";
      return $this->handleView($this->view($errors, 400));
    }

    $user->setEnabled(TRUE);
    $em->flush();

    // send token with profile so user can login immediately
    $token = $this->get('lexik_jwt_authentication.jwt_manager')->create($user);
    $user->setToken($token);

    return $this->handleView($this->view($user));
  }

  /**
   * @Post("/users/forgot-password")
   */
  public function forgotPasswordAction(Request $request) {
    $em = $this->getDoctrine()->getManager();

    $username = $request->request->get('username');

    /** @var User $user */
    $user = $em->getRepository('AppBundle:User')
      ->loadUserByUsername($username);

    if (!$user) {
      return $this->handleView($this->view(array(
        'form' => "The user '{$username}' does not exist."
      ), 400));
    }

    if (!$user->isEnabled()) {
      return $this->handleView($this->view(array(
        'form' => 'Cannot reset password of an inactive account.'
      ), 400));
    }

    $token = (new TokenGenerator())->generateToken();
    $user->setConfirmationToken($token);

    $em->flush();

    /** @var Mailer $mailer */
    $mailer = $this->get('app.mailer');
    $failedRecipients = $mailer->sendPasswordRecoveryMessage($user);

    return $this->handleView($this->view($failedRecipients));
  }

  /**
   * @Post("/users/reset-password")
   */
  public function resetPasswordAction(Request $request) {
    $em = $this->getDoctrine()->getManager();
    $validator = $this->get('validator');
    $encoder = $this->get('security.password_encoder');

    $data = $request->request->all();
    $errors = array();

    if (empty($data['token'])) {
      $errors['form'] = 'Invalid token.';
      return $this->handleView($this->view($errors, 400));
    }

    /** @var User $user */
    $user = $em->getRepository('AppBundle:User')
      ->findByConfirmationToken($data['token']);

    if (!$user) {
      $errors['form'] = "Token '{$data['token']}' not found.";
      return $this->handleView($this->view($errors, 400));
    }

    if (isset($data['plain_password'])) {
      $user->setPlainPassword($data['plain_password']);

      if (empty($data['plain_password_confirmation']) || ($data['plain_password_confirmation'] !== $data['plain_password'])
      ) {
        $pwViolation = new ConstraintViolation('Your password and password confirmation do no match.',
          NULL, array(), NULL, 'plain_password_confirmation', NULL);
      }
    }

    $violations = $validator->validate($user, NULL, array('change_password'));
    if (!empty($pwViolation)) {
      $violations->add($pwViolation);
    }

    if ($violations->count() > 0) {
      $errors = ConstraintViolationSerializer::serializeList($violations);

      return $this->handleView($this->view($errors, 400));
    }

    $user->setPassword($encoder->encodePassword($user, $user->getPlainPassword()));
    $user->eraseCredentials();
    $user->setConfirmationToken(NULL);

    $em->flush();

    return $this->handleView($this->view($user));
  }

  /**
   * @Get("/users/check-username/{username}")
   */
  public function checkUsernameAction($username) {
    $em = $this->getDoctrine()->getManager();

    $available = $em->getRepository('AppBundle:User')
      ->checkUsername($username);

    return $this->handleView($this->view($available));
  }
}
