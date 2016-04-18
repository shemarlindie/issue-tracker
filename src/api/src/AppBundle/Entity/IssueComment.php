<?php
/**
 * Created by PhpStorm.
 * User: shemarcl
 * Date: 3/13/16
 * Time: 8:41 AM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Issue
 *
 * @ORM\Table(name="issue_comments")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\IssueCommentRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class IssueComment extends Entity {
  public function __construct() {
    parent::__construct();
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
   *   inversedBy="comments",
   * )
   * @ORM\JoinColumn(
   *   name="commenter_id",
   *   referencedColumnName="id"
   * )
   */
  private $commenter;

  /**
   * @var integer
   *
   * @ORM\Column(name="commenter_id", type="bigint", nullable=false)
   */
  private $commenterId;

  /**
   * @var Project
   *
   * @ORM\ManyToOne(
   *   targetEntity="AppBundle\Entity\Issue",
   *   inversedBy="comments",
   * )
   * @ORM\JoinColumn(
   *   name="issue_id",
   *   referencedColumnName="id"
   * )
   */
  private $issue;

  /**
   * @var integer
   *
   * @ORM\Column(name="issue_id", type="bigint", nullable=false)
   */
  private $issueId;

  /**
   * @var IssueStatus
   *
   * @ORM\ManyToOne(
   *   targetEntity="AppBundle\Entity\IssueStatus",
   *   inversedBy="comments",
   * )
   * @ORM\JoinColumn(
   *   name="status_id",
   *   referencedColumnName="id"
   * )
   *
   */
  private $status;

  /**
   * @var integer
   *
   * @ORM\Column(name="status_id", type="bigint", nullable=true)
   */
  private $statusId;

  /**
   * @var string
   *
   * @ORM\Column(name="name", type="string", length=100, nullable=true)
   *
   * @Assert\Length(max="100")
   */
  private $name;

  /**
   * @var string
   *
   * @ORM\Column(name="description", type="string", length=500, nullable=true)
   *
   * @Assert\Length(max="500")
   */
  private $description;

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
   * Set commenterId
   *
   * @param integer $commenterId
   *
   * @return IssueComment
   */
  public function setCommenterId($commenterId) {
    $this->commenterId = $commenterId;

    return $this;
  }

  /**
   * Get commenterId
   *
   * @return integer
   */
  public function getCommenterId() {
    return $this->commenterId;
  }

  /**
   * Set name
   *
   * @param string $name
   *
   * @return IssueComment
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
   * @return IssueComment
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
   * Set issueId
   *
   * @param integer $issueId
   *
   * @return IssueComment
   */
  public function setIssueId($issueId) {
    $this->issueId = $issueId;

    return $this;
  }

  /**
   * Get issueId
   *
   * @return integer
   */
  public function getIssueId() {
    return $this->issueId;
  }

  /**
   * Set commenter
   *
   * @param \AppBundle\Entity\User $commenter
   *
   * @return IssueComment
   */
  public function setCommenter(\AppBundle\Entity\User $commenter = NULL) {
    $this->commenter = $commenter;

    return $this;
  }

  /**
   * Get commenter
   *
   * @return \AppBundle\Entity\User
   */
  public function getCommenter() {
    return $this->commenter;
  }

  /**
   * Set issue
   *
   * @param \AppBundle\Entity\Issue $issue
   *
   * @return IssueComment
   */
  public function setIssue(\AppBundle\Entity\Issue $issue = NULL) {
    $this->issue = $issue;

    return $this;
  }

  /**
   * Get issue
   *
   * @return \AppBundle\Entity\Issue
   */
  public function getIssue() {
    return $this->issue;
  }

  /**
   * Set statusId
   *
   * @param integer $statusId
   *
   * @return IssueComment
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
   * @return IssueComment
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
}
