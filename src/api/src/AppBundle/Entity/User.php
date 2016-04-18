<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="users", indexes={@ORM\Index(name="search_idx", columns={"first_name", "last_name", "username", "email", "company_name"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class User extends BaseUser {
  public function __construct() {
    parent::__construct();

    $this->projects = new ArrayCollection();
    $this->collaborations = new ArrayCollection();
    $this->issues = new ArrayCollection();
    $this->issuesTested = new ArrayCollection();
  }

  /**
   * @var integer
   *
   * @ORM\Column(name="id", type="bigint", nullable=false)
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   *
   * @JMS\ReadOnly()
   */
  protected $id;

  /**
   * @var ArrayCollection
   *
   * @ORM\OneToMany(
   *   targetEntity="AppBundle\Entity\Project",
   *   mappedBy="owner",
   *   fetch="EXTRA_LAZY",
   *   cascade={"remove"}
   * )
   *
   * @JMS\Exclude()
   * @JMS\ReadOnly()
   */
  private $projects;

  /**
   * @var ArrayCollection
   *
   * @ORM\ManyToMany(
   *   targetEntity="AppBundle\Entity\Project",
   *   mappedBy="collaborators",
   *   fetch="EXTRA_LAZY"
   * )
   *
   * @JMS\Exclude()
   * @JMS\ReadOnly()
   */
  private $collaborations;

  /**
   * @var ArrayCollection
   *
   * @ORM\ManyToMany(
   *   targetEntity="AppBundle\Entity\Issue",
   *   mappedBy="testers",
   *   fetch="EXTRA_LAZY"
   * )
   *
   * @JMS\Exclude()
   * @JMS\ReadOnly()
   */
  private $issuesTested;

  /**
   * @var ArrayCollection
   *
   * @ORM\ManyToMany(
   *   targetEntity="AppBundle\Entity\Issue",
   *   mappedBy="fixers",
   *   fetch="EXTRA_LAZY"
   * )
   *
   * @JMS\Exclude()
   * @JMS\ReadOnly()
   */
  private $issuesFixed;

  /**
   * @var ArrayCollection
   *
   * @ORM\OneToMany(
   *   targetEntity="AppBundle\Entity\Issue",
   *   mappedBy="reportedBy",
   *   fetch="EXTRA_LAZY"
   * )
   *
   * @JMS\Exclude()
   * @JMS\ReadOnly()
   */
  private $issues;

  /**
   * @var ArrayCollection
   *
   * @ORM\OneToMany(
   *   targetEntity="AppBundle\Entity\IssueComment",
   *   mappedBy="commenter",
   *   fetch="EXTRA_LAZY"
   * )
   *
   * @JMS\Exclude()
   * @JMS\ReadOnly()
   */
  private $comments;

  /**
   * @var string
   *
   * @ORM\Column(name="first_name", type="string", length=50, nullable=true)
   *
   * @Assert\Expression(
   *   "this.getCompanyName() or this.getFirstName()",
   *   message="Either a company name or your name is required.",
   *   groups={"registration", "profile"}
   * )
   *
   * @Assert\Length(
   *   max="50",
   *   maxMessage="Your first name must not exceed 50 characters.",
   *   groups={"registration", "profile"}
   * )
   */
  private $firstName;

  /**
   * @var string
   *
   * @ORM\Column(name="last_name", type="string", length=50, nullable=true)
   *
   * @Assert\Expression(
   *   "this.getCompanyName() or this.getLastName()",
   *   message="Either a company name or your name is required.",
   *   groups={"registration", "profile"}
   * )
   *
   * @Assert\Length(
   *   max="50",
   *   maxMessage="Your last name must not exceed 50 characters.",
   *   groups={"registration", "profile"}
   * )
   */
  private $lastName;

  /**
   * @var string
   *
   * @ORM\Column(name="company_name", type="string", length=255, nullable=true)
   *
   * @Assert\Expression(
   *   "this.getFirstName() and this.getLastName() or this.getCompanyName()",
   *   message="Either a company name or your name is required.",
   *   groups={"registration", "profile"}
   * )
   *
   * @Assert\Length(
   *   max="255",
   *   maxMessage="Your company name must not exceed 255 characters.",
   *   groups={"registration", "profile"}
   * )
   */
  private $companyName;

  /**
   * @var string
   *
   * @ORM\Column(name="address1", type="string", length=255, nullable=true)
   *
   * @Assert\Length(
   *   max=255,
   *   groups={"registration", "profile"}
   * )
   */
  private $address1;

  /**
   * @var string
   *
   * @ORM\Column(name="address2", type="string", length=255, nullable=true)
   *
   * @Assert\Length(
   *   max=255,
   *   groups={"registration", "profile"}
   * )
   */
  private $address2;

  /**
   * @var string
   *
   * @ORM\Column(name="city", type="string", length=100, nullable=true)
   *
   * @Assert\Length(
   *   max="100",
   *   groups={"registration", "profile"}
   * )
   */
  private $city;

  /**
   * @var string
   *
   * @ORM\Column(name="country", type="string", length=100, nullable=true)
   *
   * @Assert\Length(
   *   max="100",
   *   groups={"registration", "profile"}
   * )
   */
  private $country;

  /**
   * @var string
   *
   * @ORM\Column(name="phone", type="string", length=255, nullable=true)
   *
   * @Assert\Length(
   *   max="255",
   *   maxMessage="This field must not exceed 255 characters.",
   *   groups={ "registration", "profile" }
   *   )
   */
  private $phone;

  /**
   * @var string
   *
   * @ORM\Column(name="slogan", type="string", length=255, nullable=true)
   *
   * @Assert\Length(
   *   max=255,
   *   maxMessage="Your slogan must NOT exceed 255 characters.",
   *   groups={ "registration", "profile" }
   * )
   */
  private $slogan;

  /**
   * @var UserPhoto
   *
   * @ORM\OneToOne(
   *   targetEntity="AppBundle\Entity\UserPhoto",
   *   mappedBy="user",
   *   cascade={"remove"}
   * )
   *
   * @JMS\ReadOnly()
   */
  private $photo;

  /**
   * @var string
   *
   * JWT that is sent to client when profile is updated.
   */
  private $token;

  public function getFillable() {
    return array_merge(parent::getFillable(), array(
      'firstName' => 'first_name',
      'lastName' => 'last_name',
      'companyName' => 'company_name',
      'address1' => 'address1',
      'address2' => 'address2',
      'city' => 'city',
      'country' => 'country',
      'phone' => 'phone',
      'slogan' => 'slogan'
    ));
  }

  /**
   * @var void
   *
   * Removes all non-default roles. (All except ROLE_USER)
   */
  public function resetRoles() {
    $this->roles = array();
  }

  /**
   * @JMS\VirtualProperty()
   */
  public function getSimpleRoles() {
    $roles = $this->getRoles();
    $simpleRoles = array();

    foreach ($roles as $role) {
      $simpleRoles[] = strtolower(preg_replace('/(ROLE_)|(_)/', '', $role));
    }

    return $simpleRoles;
  }

  /**
   * Get firstName
   *
   * @return string
   */
  public function getFirstName() {
    return $this->firstName;
  }

  /**
   * Set firstName
   *
   * @param string $firstName
   *
   * @return User
   */
  public function setFirstName($firstName) {
    $this->firstName = $firstName;

    return $this;
  }

  /**
   * Get lastName
   *
   * @return string
   */
  public function getLastName() {
    return $this->lastName;
  }

  /**
   * Set lastName
   *
   * @param string $lastName
   *
   * @return User
   */
  public function setLastName($lastName) {
    $this->lastName = $lastName;

    return $this;
  }

  /**
   * Get companyName
   *
   * @return string
   */
  public function getCompanyName() {
    return $this->companyName;
  }

  /**
   * Set companyName
   *
   * @param string $companyName
   *
   * @return User
   */
  public function setCompanyName($companyName) {
    $this->companyName = $companyName;

    return $this;
  }

  /**
   * Get tagline
   *
   * @return string
   */
  public function getSlogan() {
    return $this->slogan;
  }

  /**
   * Set tagline
   *
   * @param string $slogan
   *
   * @return User
   */
  public function setSlogan($slogan) {
    $this->slogan = $slogan;

    return $this;
  }

  /**
   * @return string
   *
   * @JMS\VirtualProperty()
   */
  public function getFullName() {
    $name = '';

    if ($this->getFirstName() != NULL) {
      $name .= $this->getFirstName();
    }
    if ($this->getLastName() != NULL) {
      $name .= " {$this->getLastName()}";
    }

    if (empty($name)) {
      $name = $this->getCompanyName();
    }

    if (empty($name)) {
      $name = $this->getUsername();
    }

    return $name;
  }

  /**
   * @JMS\VirtualProperty()
   */
  public function getInitials() {
    $initials = '';
    $parts = explode(' ', $this->getFullName());

    foreach ($parts as $part) {
      $initials .= ucfirst(substr($part, 0, 1));
    }

    return $initials;
  }

  /**
   * Set address1
   *
   * @param string $address1
   *
   * @return User
   */
  public function setAddress1($address1) {
    $this->address1 = $address1;

    return $this;
  }

  /**
   * Get address1
   *
   * @return string
   */
  public function getAddress1() {
    return $this->address1;
  }

  /**
   * Set address2
   *
   * @param string $address2
   *
   * @return User
   */
  public function setAddress2($address2) {
    $this->address2 = $address2;

    return $this;
  }

  /**
   * Get address2
   *
   * @return string
   */
  public function getAddress2() {
    return $this->address2;
  }

  /**
   * Set phone
   *
   * @param string $phone
   *
   * @return User
   */
  public function setPhone($phone) {
    $this->phone = $phone;

    return $this;
  }

  /**
   * Get phone
   *
   * @return string
   */
  public function getPhone() {
    return $this->phone;
  }

  /**
   * Get enabled
   *
   * @return boolean
   */
  public function getEnabled() {
    return $this->enabled;
  }

  /**
   * Set salt
   *
   * @param string $salt
   *
   * @return User
   */
  public function setSalt($salt) {
    $this->salt = $salt;

    return $this;
  }

  /**
   * Get locked
   *
   * @return boolean
   */
  public function getLocked() {
    return $this->locked;
  }

  /**
   * Get expired
   *
   * @return boolean
   */
  public function getExpired() {
    return $this->expired;
  }

  /**
   * Get expiresAt
   *
   * @return \DateTime
   */
  public function getExpiresAt() {
    return $this->expiresAt;
  }

  /**
   * Get credentialsExpired
   *
   * @return boolean
   */
  public function getCredentialsExpired() {
    return $this->credentialsExpired;
  }

  /**
   * Get credentialsExpireAt
   *
   * @return \DateTime
   */
  public function getCredentialsExpireAt() {
    return $this->credentialsExpireAt;
  }

  /**
   * Set photo
   *
   * @param \AppBundle\Entity\UserPhoto $photo
   *
   * @return User
   */
  public function setPhoto(UserPhoto $photo = NULL) {
    $this->photo = $photo;

    return $this;
  }

  /**
   * Get photo
   *
   * @return UserPhoto
   */
  public function getPhoto() {
    return $this->photo;
  }

  /**
   * @return string
   */
  public function getToken() {
    return $this->token;
  }

  /**
   * @param string $token
   */
  public function setToken($token) {
    $this->token = $token;
  }


  /**
   * Set city
   *
   * @param string $city
   *
   * @return User
   */
  public function setCity($city) {
    $this->city = $city;

    return $this;
  }

  /**
   * Get city
   *
   * @return string
   */
  public function getCity() {
    return $this->city;
  }

  /**
   * Set country
   *
   * @param string $country
   *
   * @return User
   */
  public function setCountry($country) {
    $this->country = $country;

    return $this;
  }

  /**
   * Get country
   *
   * @return string
   */
  public function getCountry() {
    return $this->country;
  }

    /**
     * Add project
     *
     * @param \AppBundle\Entity\Project $project
     *
     * @return User
     */
    public function addProject(\AppBundle\Entity\Project $project)
    {
        $this->projects[] = $project;

        return $this;
    }

    /**
     * Remove project
     *
     * @param \AppBundle\Entity\Project $project
     */
    public function removeProject(\AppBundle\Entity\Project $project)
    {
        $this->projects->removeElement($project);
    }

    /**
     * Get projects
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Add collaboration
     *
     * @param \AppBundle\Entity\Project $collaboration
     *
     * @return User
     */
    public function addCollaboration(\AppBundle\Entity\Project $collaboration)
    {
        $this->collaborations[] = $collaboration;

        return $this;
    }

    /**
     * Remove collaboration
     *
     * @param \AppBundle\Entity\Project $collaboration
     */
    public function removeCollaboration(\AppBundle\Entity\Project $collaboration)
    {
        $this->collaborations->removeElement($collaboration);
    }

    /**
     * Get collaborations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCollaborations()
    {
        return $this->collaborations;
    }

    /**
     * Add issue
     *
     * @param \AppBundle\Entity\Issue $issue
     *
     * @return User
     */
    public function addIssue(\AppBundle\Entity\Issue $issue)
    {
        $this->issues[] = $issue;

        return $this;
    }

    /**
     * Remove issue
     *
     * @param \AppBundle\Entity\Issue $issue
     */
    public function removeIssue(\AppBundle\Entity\Issue $issue)
    {
        $this->issues->removeElement($issue);
    }

    /**
     * Get issues
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIssues()
    {
        return $this->issues;
    }

    /**
     * Add issuesTested
     *
     * @param \AppBundle\Entity\Issue $issuesTested
     *
     * @return User
     */
    public function addIssuesTested(\AppBundle\Entity\Issue $issuesTested)
    {
        $this->issuesTested[] = $issuesTested;

        return $this;
    }

    /**
     * Remove issuesTested
     *
     * @param \AppBundle\Entity\Issue $issuesTested
     */
    public function removeIssuesTested(\AppBundle\Entity\Issue $issuesTested)
    {
        $this->issuesTested->removeElement($issuesTested);
    }

    /**
     * Get issuesTested
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIssuesTested()
    {
        return $this->issuesTested;
    }

    /**
     * Add issuesFixed
     *
     * @param \AppBundle\Entity\Issue $issuesFixed
     *
     * @return User
     */
    public function addIssuesFixed(\AppBundle\Entity\Issue $issuesFixed)
    {
        $this->issuesFixed[] = $issuesFixed;

        return $this;
    }

    /**
     * Remove issuesFixed
     *
     * @param \AppBundle\Entity\Issue $issuesFixed
     */
    public function removeIssuesFixed(\AppBundle\Entity\Issue $issuesFixed)
    {
        $this->issuesFixed->removeElement($issuesFixed);
    }

    /**
     * Get issuesFixed
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIssuesFixed()
    {
        return $this->issuesFixed;
    }

    /**
     * Add comment
     *
     * @param \AppBundle\Entity\IssueComment $comment
     *
     * @return User
     */
    public function addComment(\AppBundle\Entity\IssueComment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \AppBundle\Entity\IssueComment $comment
     */
    public function removeComment(\AppBundle\Entity\IssueComment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }
}
