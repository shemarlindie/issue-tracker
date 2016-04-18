<?php
/**
 * Created by PhpStorm.
 * User: shemarcl
 * Date: 3/13/16
 * Time: 8:41 AM
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Project
 *
 * @ORM\Table(name="projects")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProjectRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class Project extends Entity {
  public function __construct() {
    parent::__construct();

    $this->collaborators = new ArrayCollection();
    $this->issues = new ArrayCollection();
    $this->tags = new ArrayCollection();
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
   * @var string
   *
   * @ORM\Column(name="name", type="string", length=100, nullable=false)
   *
   * @Assert\NotBlank()
   * @Assert\Length(max="100")
   */
  private $name;

  /**
   * @var string
   *
   * @ORM\Column(name="client", type="string", length=100, nullable=true)
   *
   * @Assert\Length(max="100")
   */
  private $client;

  /**
   * @var string
   *
   * @ORM\Column(name="description", type="string", length=500, nullable=true)
   *
   * @Assert\Length(max="500")
   */
  private $description;

  /**
   * @var User
   *
   * @ORM\ManyToOne(
   *   targetEntity="AppBundle\Entity\User",
   *   inversedBy="projects",
   * )
   * @ORM\JoinColumn(
   *   name="owner_id",
   *   referencedColumnName="id"
   * )
   *
   */
  private $owner;

  /**
   * @var integer
   *
   * @ORM\Column(name="owner_id", type="bigint", nullable=false)
   */
  private $ownerId;

  /**
   * @var ArrayCollection
   *
   * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", inversedBy="collaborations")
   * @ORM\JoinTable(
   *   name="project_collaborators",
   *   joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
   *   inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
   * )
   */
  private $collaborators;

  /**
   * @var ArrayCollection
   *
   * @ORM\OneToMany(
   *   targetEntity="AppBundle\Entity\Issue",
   *   mappedBy="project",
   *   fetch="EXTRA_LAZY",
   *   cascade={"remove"}
   * )
   *
   * @JMS\Exclude()
   * @JMS\ReadOnly()
   */
  private $issues;

  /**
   * @var ArrayCollection
   *
   * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Tag", inversedBy="projects")
   * @ORM\JoinTable(
   *   name="project_tags",
   *   joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
   *   inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
   * )
   */
  private $tags;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="date_due", type="datetime", nullable=true)
   */
  private $dateDue;

  public function getFillable() {
    return array_merge(parent::getFillable(), array(
      'name',
      'client',
      'description'
    ));
  }

  /**
   * @JMS\VirtualProperty()
   */
  public function getIssueCount() {
    return $this->issues->count();
  }


  /**
   * Get id
   *
   * @return integer
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Set name
   *
   * @param string $name
   *
   * @return Project
   */
  public function setName($name) {
    $this->name = $name;

    return $this;
  }

  /**
   * Get name
   *
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Set description
   *
   * @param string $description
   *
   * @return Project
   */
  public function setDescription($description) {
    $this->description = $description;

    return $this;
  }

  /**
   * Get description
   *
   * @return string
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * Set ownerId
   *
   * @param integer $ownerId
   *
   * @return Project
   */
  public function setOwnerId($ownerId) {
    $this->ownerId = $ownerId;

    return $this;
  }

  /**
   * Get ownerId
   *
   * @return integer
   */
  public function getOwnerId() {
    return $this->ownerId;
  }

  /**
   * Set owner
   *
   * @param \AppBundle\Entity\User $owner
   *
   * @return Project
   */
  public function setOwner(\AppBundle\Entity\User $owner = NULL) {
    $this->owner = $owner;

    return $this;
  }

  /**
   * Get owner
   *
   * @return \AppBundle\Entity\User
   */
  public function getOwner() {
    return $this->owner;
  }

  /**
   * Add collaborator
   *
   * @param \AppBundle\Entity\User $collaborator
   *
   * @return Project
   */
  public function addCollaborator(\AppBundle\Entity\User $collaborator) {
    $this->collaborators[] = $collaborator;

    return $this;
  }

  /**
   * Remove collaborator
   *
   * @param \AppBundle\Entity\User $collaborator
   */
  public function removeCollaborator(\AppBundle\Entity\User $collaborator) {
    $this->collaborators->removeElement($collaborator);
  }

  /**
   * Get collaborators
   *
   * @return \Doctrine\Common\Collections\Collection
   */
  public function getCollaborators() {
    return $this->collaborators;
  }

  /**
   * Add issue
   *
   * @param \AppBundle\Entity\Issue $issue
   *
   * @return Project
   */
  public function addIssue(\AppBundle\Entity\Issue $issue) {
    $this->issues[] = $issue;

    return $this;
  }

  /**
   * Remove issue
   *
   * @param \AppBundle\Entity\Issue $issue
   */
  public function removeIssue(\AppBundle\Entity\Issue $issue) {
    $this->issues->removeElement($issue);
  }

  /**
   * Get issues
   *
   * @return \Doctrine\Common\Collections\Collection
   */
  public function getIssues() {
    return $this->issues;
  }

  /**
   * Add tag
   *
   * @param \AppBundle\Entity\Tag $tag
   *
   * @return Project
   */
  public function addTag(\AppBundle\Entity\Tag $tag) {
    $this->tags[] = $tag;

    return $this;
  }

  /**
   * Remove tag
   *
   * @param \AppBundle\Entity\Tag $tag
   */
  public function removeTag(\AppBundle\Entity\Tag $tag) {
    $this->tags->removeElement($tag);
  }

  /**
   * Get tags
   *
   * @return \Doctrine\Common\Collections\Collection
   */
  public function getTags() {
    return $this->tags;
  }

  /**
   * Set dateDue
   *
   * @param \DateTime $dateDue
   *
   * @return Project
   */
  public function setDateDue($dateDue) {
    $this->dateDue = $dateDue;

    return $this;
  }

  /**
   * Get dateDue
   *
   * @return \DateTime
   */
  public function getDateDue() {
    return $this->dateDue;
  }

  public function setCollaborators($collaborators) {
    $this->collaborators = $collaborators;
  }

    /**
     * Set client
     *
     * @param string $client
     *
     * @return Project
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return string
     */
    public function getClient()
    {
        return $this->client;
    }
}
