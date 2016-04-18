<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity(
 *   "usernameCanonical",
 *   message="A user with the specified username already exists.",
 *   errorPath="username",
 *   groups={"registration", "profile"}
 * )
 *
 * @UniqueEntity(
 *   "emailCanonical",
 *   message="A user with the specified email already exists.",
 *   errorPath="email",
 *   groups={"registration", "profile"}
 * )
 *
 * @ORM\HasLifecycleCallbacks()
 */
abstract class BaseUser extends Entity implements UserInterface {
  /**
   * @var integer
   *
   * @ORM\Id
   * @ORM\Column(name="id", type="bigint", unique=true, nullable=false)
   * @ORM\GeneratedValue(strategy="AUTO")
   *
   * @JMS\ReadOnly()
   */
  protected $id;

  /**
   * @var string
   *
   * @ORM\Column(name="username", type="string", length=50, unique=true, nullable=false)
   *
   * @Assert\NotBlank(
   *   groups={"registration", "profile"}
   * )
   * @Assert\Length(
   *   min="2",
   *   max="50",
   *   groups={"registration", "profile"}
   * )
   * @Assert\Regex(pattern="[a-zA-Z0-9_]+")
   */
  protected $username;

  /**
   * @var string
   *
   * @ORM\Column(name="username_canonical", type="string", length=50, unique=true, nullable=false)
   */
  protected $usernameCanonical;

  /**
   * @var string
   *
   * @ORM\Column(name="email", type="string", length=255, unique=true, nullable=false)
   *
   * @Assert\NotBlank(
   *   groups={"registration", "profile"}
   * )
   * @Assert\Length(
   *   max="255",
   *   groups={"registration", "profile"}
   * )
   * @Assert\Email(
   *   groups={"registration", "profile"}
   * )
   */
  protected $email;

  /**
   * @var string
   *
   * @ORM\Column(name="email_canonical", type="string", length=255, unique=true, nullable=false)
   */
  protected $emailCanonical;

  /**
   * @var boolean
   *
   * @ORM\Column(name="enabled", type="boolean", nullable=false)
   */
  protected $enabled;

  /**
   * The salt to use for hashing
   *
   * @var string
   *
   * @ORM\Column(name="salt", type="string", length=255, nullable=true)
   *
   * @JMS\Exclude()
   */
  protected $salt;

  /**
   * Encrypted password. Must be persisted.
   *
   * @var string
   *
   * @ORM\Column(name="password", type="string", length=255, nullable=false)
   *
   * @JMS\Exclude()
   */
  protected $password;

  /**
   * Plain password.
   *
   * @var string
   *
   * @Assert\NotBlank(
   *   groups={"registration", "change_password"}
   * )
   *
   * @Assert\Length(
   *   min="6",
   *   max="50",
   *   groups={"registration", "change_password"}
   * )
   *
   * @JMS\Exclude()
   */
  protected $plainPassword;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="last_login", type="datetime", nullable=true)
   */
  protected $lastLogin;

  /**
   * Random string sent to the user email address in order to verify it
   *
   * @var string
   *
   * @ORM\Column(name="confirmation_token", type="string", nullable=true)
   *
   * @JMS\Exclude()
   */
  protected $confirmationToken;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="password_requested_at", type="datetime", nullable=true)
   */
  protected $passwordRequestedAt;

  /**
   * @var boolean
   *
   * @ORM\Column(name="locked", type="boolean", nullable=false)
   */
  protected $locked;

  /**
   * @var boolean
   *
   * @ORM\Column(name="expired", type="boolean", nullable=false)
   */
  protected $expired;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="expires_at", type="datetime", nullable=true)
   */
  protected $expiresAt;

  /**
   * @var array
   *
   * @ORM\Column(name="roles", type="json_array", nullable=false)
   *
   * @JMS\Exclude()
   */
  protected $roles;

  /**
   * @var boolean
   *
   * @ORM\Column(name="credentials_expired", type="boolean", nullable=false)
   */
  protected $credentialsExpired;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="credentials_expire_at", type="datetime", nullable=true)
   */
  protected $credentialsExpireAt;

  public function __construct() {
    parent::__construct();
    $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    $this->enabled = false;
    $this->locked = false;
    $this->expired = false;
    $this->roles = array();
    $this->credentialsExpired = false;
  }

  public function getFillable() {
    return array_merge(parent::getFillable(), array(
      'username' => 'username',
      'email' => 'email'
    ));
  }


  /**
   * @ORM\PrePersist()
   * @ORM\PreUpdate()
   */
  public function updateCanonicalFields() {
    $this->setEmailCanonical(mb_strtolower($this->getEmail()));
    $this->setUsernameCanonical(mb_strtolower($this->getUsername()));
  }

  public function addRole($role) {
    $role = strtoupper($role);
    if ($role === static::ROLE_DEFAULT) {
      return $this;
    }

    if (!in_array($role, $this->roles, true)) {
      $this->roles[] = $role;
    }

    return $this;
  }

  /**
   * Serializes the user.
   *
   * The serialized data have to contain the fields used during check for
   * changes and the id.
   *
   * @return string
   */
  public function serialize() {
    return serialize(array(
      $this->password,
      $this->salt,
      $this->usernameCanonical,
      $this->username,
      $this->expired,
      $this->locked,
      $this->credentialsExpired,
      $this->enabled,
      $this->id,
      $this->expiresAt,
      $this->credentialsExpireAt,
      $this->email,
      $this->emailCanonical,
    ));
  }

  /**
   * Unserializes the user.
   *
   * @param string $serialized
   */
  public function unserialize($serialized) {
    $data = unserialize($serialized);
    // add a few extra elements in the array to ensure that we have enough keys when unserializing
    // older data which does not include all properties.
    $data = array_merge($data, array_fill(0, 2, null));

    list(
      $this->password,
      $this->salt,
      $this->usernameCanonical,
      $this->username,
      $this->expired,
      $this->locked,
      $this->credentialsExpired,
      $this->enabled,
      $this->id,
      $this->expiresAt,
      $this->credentialsExpireAt,
      $this->email,
      $this->emailCanonical
      ) = $data;
  }

  /**
   * Removes sensitive data from the user.
   */
  public function eraseCredentials() {
    $this->plainPassword = null;
  }

  /**
   * {@inheritDoc}
   */
  public function getId() {
    return $this->id;
  }

  public function getUsername() {
    return $this->username;
  }

  public function getUsernameCanonical() {
    return $this->usernameCanonical;
  }

  public function getSalt() {
    return $this->salt;
  }

  public function getEmail() {
    return $this->email;
  }

  public function getEmailCanonical() {
    return $this->emailCanonical;
  }

  /**
   * Gets the encrypted password.
   *
   * @return string
   */
  public function getPassword() {
    return $this->password;
  }

  public function getPlainPassword() {
    return $this->plainPassword;
  }

  /**
   * Gets the last login time.
   *
   * @return \DateTime
   *
   */
  public function getLastLogin() {
    return $this->lastLogin;
  }

  public function getConfirmationToken() {
    return $this->confirmationToken;
  }

  /**
   * Returns the user roles
   *
   * @return array The roles
   *
   * @JMS\VirtualProperty()
   */
  public function getRoles() {
    $roles = $this->roles;

    // we need to make sure to have at least one role
    $roles[] = static::ROLE_DEFAULT;

    return array_unique($roles);
  }

  /**
   * Never use this to check if this user has access to anything!
   *
   * Use the SecurityContext, or an implementation of AccessDecisionManager
   * instead, e.g.
   *
   *         $securityContext->isGranted('ROLE_USER');
   *
   * @param string $role
   *
   * @return boolean
   */
  public function hasRole($role) {
    return in_array(strtoupper($role), $this->getRoles(), true);
  }

  public function isAccountNonExpired() {
    if (true === $this->expired) {
      return false;
    }

    if (null !== $this->expiresAt && $this->expiresAt->getTimestamp() < time()) {
      return false;
    }

    return true;
  }

  public function isAccountNonLocked() {
    return !$this->locked;
  }

  public function isCredentialsNonExpired() {
    if (true === $this->credentialsExpired) {
      return false;
    }

    if (null !== $this->credentialsExpireAt && $this->credentialsExpireAt->getTimestamp() < time()) {
      return false;
    }

    return true;
  }

  /**
   * @return bool
   *
   */
  public function isCredentialsExpired() {
    return !$this->isCredentialsNonExpired();
  }

  /**
   * @return bool
   *
   */
  public function isEnabled() {
    return $this->enabled;
  }

  /**
   * @return bool
   *
   */
  public function isExpired() {
    return !$this->isAccountNonExpired();
  }

  /**
   * @return bool
   *
   */
  public function isLocked() {
    return !$this->isAccountNonLocked();
  }

  /**
   * @return bool
   *
   * @JMS\VirtualProperty()
   */
  public function isSuperAdmin() {
    return $this->hasRole(static::ROLE_SUPER_ADMIN);
  }

  public function removeRole($role) {
    if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
      unset($this->roles[$key]);
      $this->roles = array_values($this->roles);
    }

    return $this;
  }

  public function setUsername($username) {
    $this->username = $username;

    return $this;
  }

  public function setUsernameCanonical($usernameCanonical) {
    $this->usernameCanonical = $usernameCanonical;

    return $this;
  }

  /**
   * @param \DateTime $date
   *
   * @return User
   */
  public function setCredentialsExpireAt(\DateTime $date = null) {
    $this->credentialsExpireAt = $date;

    return $this;
  }

  /**
   * @param boolean $boolean
   *
   * @return User
   */
  public function setCredentialsExpired($boolean) {
    $this->credentialsExpired = $boolean;

    return $this;
  }

  public function setEmail($email) {
    $this->email = $email;

    return $this;
  }

  public function setEmailCanonical($emailCanonical) {
    $this->emailCanonical = $emailCanonical;

    return $this;
  }

  public function setEnabled($boolean) {
    $this->enabled = (Boolean)$boolean;
    $this->setConfirmationToken(null); // reset token if activation status changes

    return $this;
  }

  /**
   * Sets this user to expired.
   *
   * @param Boolean $boolean
   *
   * @return User
   */
  public function setExpired($boolean) {
    $this->expired = (Boolean)$boolean;

    return $this;
  }

  /**
   * @param \DateTime $date
   *
   * @return User
   */
  public function setExpiresAt(\DateTime $date = null) {
    $this->expiresAt = $date;

    return $this;
  }

  public function setPassword($password) {
    $this->password = $password;

    return $this;
  }

  public function setSuperAdmin($boolean) {
    if (true === $boolean) {
      $this->addRole(static::ROLE_SUPER_ADMIN);
    } else {
      $this->removeRole(static::ROLE_SUPER_ADMIN);
    }

    return $this;
  }

  public function setPlainPassword($password) {
    $this->plainPassword = $password;

    return $this;
  }

  public function setLastLogin(\DateTime $time = null) {
    $this->lastLogin = $time;

    return $this;
  }

  public function setLocked($boolean) {
    $this->locked = $boolean;

    return $this;
  }

  public function setConfirmationToken($confirmationToken) {
    $this->confirmationToken = $confirmationToken;

    return $this;
  }

  public function setPasswordRequestedAt(\DateTime $date = null) {
    $this->passwordRequestedAt = $date;

    return $this;
  }

  /**
   * Gets the timestamp that the user requested a password reset.
   *
   * @return null|\DateTime
   */
  public function getPasswordRequestedAt() {
    return $this->passwordRequestedAt;
  }

  public function isPasswordRequestNonExpired($ttl) {
    return $this->getPasswordRequestedAt() instanceof \DateTime &&
    $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
  }

  public function setRoles(array $roles) {
    $this->roles = array();

    foreach ($roles as $role) {
      $this->addRole($role);
    }

    return $this;
  }

  public function __toString() {
    return (string)$this->getUsername();
  }
}
