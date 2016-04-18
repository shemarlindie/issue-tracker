<?php
/**
 * Created by PhpStorm.
 * User: shemarcl
 * Date: 3/26/16
 * Time: 10:13 AM
 */

namespace AppBundle\Handler;


use AppBundle\Util\EnhancedJsonResponse;
use JMS\Serializer\Serializer;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticationSuccessHandler extends \Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler {
  /** @var Serializer $serializer */
  protected $serializer;

  /**
   * @param JWTManager $jwtManager
   * @param EventDispatcherInterface $dispatcher
   */
  public function __construct(JWTManager $jwtManager, EventDispatcherInterface $dispatcher, Serializer $serializer) {
    parent::__construct($jwtManager, $dispatcher);
    $this->serializer = $serializer;
  }

  /**
   * {@inheritDoc}
   */
  public function onAuthenticationSuccess(Request $request, TokenInterface $token) {
    $user = $token->getUser();
    $jwt = $this->jwtManager->create($user);

    $data = array(
      'token' => $jwt,
      'user' => $user
    );
    $response = new EnhancedJsonResponse(null, $this->serializer);
    $event = new AuthenticationSuccessEvent($data, $user, $request, $response);

    $this->dispatcher->dispatch(Events::AUTHENTICATION_SUCCESS, $event);
    $response->setData($event->getData());

    return $response;
  }
}