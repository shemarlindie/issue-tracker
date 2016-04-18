<?php
/**
 * Created by PhpStorm.
 * User: shemarcl
 * Date: 4/11/16
 * Time: 10:37 PM
 */

namespace AppBundle\Util;


use AppBundle\Entity\Rental;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine;
use Symfony\Bundle\TwigBundle\TwigEngine;

class Mailer {
  private $sender = null;
  private $sender_name = null;

  /**
   * @var \Swift_Mailer $mailer
   */
  private $mailer;

  /** @var TwigEngine $renderer */
  private $renderer;

  /**
   * Mailer constructor.
   * @param \Swift_Mailer $mailer
   * @param TwigEngine $renderer
   * @param string $sender
   * @param string $sender_name
   */
  public function __construct(\Swift_Mailer $mailer, TwigEngine $renderer, $sender, $sender_name) {
    $this->sender = $sender;
    $this->sender_name = $sender_name;
    $this->mailer = $mailer;
    $this->renderer = $renderer;
  }

  public function sendAccountActivationMessage(User $user) {
    return $this->sendMessage(
      'Activate your account',
      $this->sender, $this->sender_name,
      $user->getEmail(), $user->getFullName(),
      'email/activate.account.html.twig',
      array('user' => $user)
    );
  }

  public function sendPasswordRecoveryMessage(User $user) {

    return $this->sendMessage(
      'Reset your password',
      $this->sender, $this->sender_name,
      $user->getEmail(), $user->getFullName(),
      'email/reset.password.html.twig',
      array('user' => $user)
    );
  }

  public function sendMessage($subject, $fromEmail, $fromName, $toEmail, $toName, $template, $data) {
    /** @var \Swift_Message $message */
    $message = \Swift_Message::newInstance()
      ->setSubject($subject)
      ->setFrom($fromEmail, $fromName)
      ->setSender($fromEmail, $fromName)
      ->setReturnPath($fromEmail)
      ->setTo($toEmail, $toName)
      ->setBody(
        $this->renderer->render($template, $data),
        'text/html'
      );

    $failedRecipients = array();
    $this->mailer->send($message, $failedRecipients);

    return $failedRecipients;
  }
}