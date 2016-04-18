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
 * Issue
 *
 * @ORM\Table(name="issues")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\IssueRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class Issue extends Entity {
  public function __construct() {
    parent::__construct();

    $this->testers = new ArrayCollection();
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
   * @var User
   *
   * @ORM\ManyToOne(
   *   targetEntity="AppBundle\Entity\User",
   *   inversedBy="issues",
   * )
   * @ORM\JoinColumn(
   *   name="reported_by_id",
   *   referencedColumnName="id"
   * )
   */
  private $reportedBy;

  /**
   * @var integer
   *
   * @ORM\Column(name="reported_by_id", type="bigint", nullable=false)
   */
  private $reportedById;

  /**
   * @var ArrayCollection
   *
   * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", inversedBy="issuesTested")
   * @ORM\JoinTable(
   *   name="issue_testers",
   *   joinColumns={@ORM\JoinColumn(name="issue_id", referencedColumnName="id")},
   *   inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
   * )
   */
  private $testers;

  /**
   * @var ArrayCollection
   *
   * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", inversedBy="issuesFixed")
   * @ORM\JoinTable(
   *   name="issue_fixers",
   *   joinColumns={@ORM\JoinColumn(name="issue_id", referencedColumnName="id")},
   *   inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
   * )
   */
  private $fixers;

  /**
   * @var Project
   *
   * @ORM\ManyToOne(
   *   targetEntity="AppBundle\Entity\Project",
   *   inversedBy="issues",
   * )
   * @ORM\JoinColumn(
   *   name="project_id",
   *   referencedColumnName="id"
   * )
   */
  private $project;

  /**
   * @var integer
   *
   * @ORM\Column(name="project_id", type="bigint", nullable=false)
   */
  private $projectId;

  /**
   * @var ArrayCollection
   *
   * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Tag", inversedBy="issues")
   * @ORM\JoinTable(
   *   name="issue_tags",
   *   joinColumns={@ORM\JoinColumn(name="issue_id", referencedColumnName="id")},
   *   inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
   * )
   */
  private $tags;

  /**
   * @var ArrayCollection
   *
   * @ORM\OneToMany(
   *   targetEntity="AppBundle\Entity\IssueComment",
   *   mappedBy="issue",
   *   fetch="EXTRA_LAZY"
   * )
   *
   * @JMS\Exclude()
   * @JMS\ReadOnly()
   */
  private $comments;

  /**
   * @var IssueStatus
   *
   * @ORM\ManyToOne(
   *   targetEntity="AppBundle\Entity\IssueStatus",
   *   inversedBy="issues",
   * )
   * @ORM\JoinColumn(
   *   name="status_id",
   *   referencedColumnName="id"
   * )
   *
   * @Assert\NotNull()
   */
  private $status;

  /**
   * @var integer
   *
   * @ORM\Column(name="status_id", type="bigint", nullable=false)
   */
  private $statusId;

  /**
   * @var IssueStatus
   *
   * @ORM\ManyToOne(
   *   targetEntity="AppBundle\Entity\IssuePriority",
   *   inversedBy="issues",
   * )
   * @ORM\JoinColumn(
   *   name="priority_id",
   *   referencedColumnName="id"
   * )
   *
   * @Assert\NotNull()
   */
  private $priority;

  /**
   * @var integer
   *
   * @ORM\Column(name="priority_id", type="bigint", nullable=false)
   */
  private $priorityId;

  /**
   * @var IssueType
   *
   * @ORM\ManyToOne(
   *   targetEntity="AppBundle\Entity\IssueType",
   *   inversedBy="issues",
   * )
   * @ORM\JoinColumn(
   *   name="type_id",
   *   referencedColumnName="id"
   * )
   *
   * @Assert\NotNull()
   */
  private $type;

  /**
   * @var integer
   *
   * @ORM\Column(name="type_id", type="bigint", nullable=false)
   */
  private $typeId;

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
   * @ORM\Column(name="description", type="string", length=500, nullable=true)
   *
   * @Assert\NotBlank()
   * @Assert\Length(max="500")
   */
  private $description;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="date_due", type="datetime", nullable=true)
   */
  private $dateDue;

  public function getFillable() {
    return array_merge(parent::getFillable(), array(
      'name',
      'description'
    ));
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
   * Set reportedById
   *
   * @param integer $reportedById
   *
   * @return Issue
   */
  public function setReportedById($reportedById) {
    $this->reportedById = $reportedById;

    return $this;
  }

  /**
   * Get reportedById
   *
   * @return integer
   */
  public function getReportedById() {
    return $this->reportedById;
  }

  /**
   * Set projectId
   *
   * @param integer $projectId
   *
   * @return Issue
   */
  public function setProjectId($projectId) {
    $this->projectId = $projectId;

    return $this;
  }

  /**
   * Get projectId
   *
   * @return integer
   */
  public function getProjectId() {
    return $this->projectId;
  }

  /**
   * Set reportedBy
   *
   * @param \AppBundle\Entity\User $reportedBy
   *
   * @return Issue
   */
  public function setReportedBy(\AppBundle\Entity\User $reportedBy = NULL) {
    $this->reportedBy = $reportedBy;

    return $this;
  }

  /**
   * Get reportedBy
   *
   * @return \AppBundle\Entity\User
   */
  public function getReportedBy() {
    return $this->reportedBy;
  }

  /**
   * Set project
   *
   * @param \AppBundle\Entity\Project $project
   *
   * @return Issue
   */
  public function setProject(\AppBundle\Entity\Project $project = NULL) {
    $this->project = $project;

    return $this;
  }

  /**
   * Get project
   *
   * @return \AppBundle\Entity\Project
   */
  public function getProject() {
    return $this->project;
  }

  /**
   * Add tag
   *
   * @param \AppBundle\Entity\Tag $tag
   *
   * @return Issue
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
   * Set statusId
   *
   * @param integer $statusId
   *
   * @return Issue
   */
  public function setStatusId($statusId) {
    $this->statusId = $statusId;

    return $this;
  }

  /**
   * Get statusId
   *
   * @return integer
   */
  public function getStatusId() {
    return $this->statusId;
  }

  /**
   * Set status
   *
   * @param \AppBundle\Entity\IssueStatus $status
   *
   * @return Issue
   */
  public function setStatus(\AppBundle\Entity\IssueStatus $status = NULL) {
    $this->status = $status;

    return $this;
  }

  /**
   * Get status
   *
   * @return \AppBundle\Entity\IssueStatus
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * Set priorityId
   *
   * @param integer $priorityId
   *
   * @return Issue
   */
  public function setPriorityId($priorityId) {
    $this->priorityId = $priorityId;

    return $this;
  }

  /**
   * Get priorityId
   *
   * @return integer
   */
  public function getPriorityId() {
    return $this->priorityId;
  }

  /**
   * Set priority
   *
   * @param \AppBundle\Entity\IssuePriority $priority
   *
   * @return Issue
   */
  public function setPriority(\AppBundle\Entity\IssuePriority $priority = NULL) {
    $this->priority = $priority;

    return $this;
  }

  /**
   * Get priority
   *
   * @return \AppBundle\Entity\IssuePriority
   */
  public function getPriority() {
    return $this->priority;
  }

  /**
   * Set typeId
   *
   * @param integer $typeId
   *
   * @return Issue
   */
  public function setTypeId($typeId) {
    $this->typeId = $typeId;

    return $this;
  }

  /**
   * Get typeId
   *
   * @return integer
   */
  public function getTypeId() {
    return $this->typeId;
  }

  /**
   * Set type
   *
   * @param \AppBundle\Entity\IssueType $type
   *
   * @return Issue
   */
  public function setType(\AppBundle\Entity\IssueType $type = NULL) {
    $this->type = $type;

    return $this;
  }

  /**
   * Get type
   *
   * @return \AppBundle\Entity\IssueType
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Set name
   *
   * @param string $name
   *
   * @return Issue
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
   * @return Issue
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
   * Add tester
   *
   * @param \AppBundle\Entity\User $tester
   *
   * @return Issue
   */
  public function addTester(\AppBundle\Entity\User $tester) {
    $this->testers[] = $tester;

    return $this;
  }

  /**
   * Remove tester
   *
   * @param \AppBundle\Entity\User $tester
   */
  public function removeTester(\AppBundle\Entity\User $tester) {
    $this->testers->removeElement($tester);
  }

  /**
   * Get testers
   *
   * @return \Doctrine\Common\Collections\Collection
   */
  public function getTesters() {
    return $this->testers;
  }

  /**
   * Add fixer
   *
   * @param \AppBundle\Entity\User $fixer
   *
   * @return Issue
   */
  public function addFixer(\AppBundle\Entity\User $fixer) {
    $this->fixers[] = $fixer;

    return $this;
  }

  /**
   * Remove fixer
   *
   * @param \AppBundle\Entity\User $fixer
   */
  public function removeFixer(\AppBundle\Entity\User $fixer) {
    $this->fixers->removeElement($fixer);
  }

  /**
   * Get fixers
   *
   * @return \Doctrine\Common\Collections\Collection
   */
  public function getFixers() {
    return $this->fixers;
  }

  public function setTesters($testers) {
    $this->testers = $testers;
  }

  public function setFixers($fixers) {
    $this->fixers = $fixers;
  }

    /**
     * Add comment
     *
     * @param \AppBundle\Entity\IssueComment $comment
     *
     * @return Issue
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

    /**
     * Set dateDue
     *
     * @param \DateTime $dateDue
     *
     * @return Issue
     */
    public function setDateDue($dateDue)
    {
        $this->dateDue = $dateDue;

        return $this;
    }

    /**
     * Get dateDue
     *
     * @return \DateTime
     */
    public function getDateDue()
    {
        return $this->dateDue;
    }
}
