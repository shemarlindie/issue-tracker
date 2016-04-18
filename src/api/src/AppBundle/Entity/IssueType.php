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
 * IssueType
 *
 * @ORM\Table(name="issue_types")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\IssueTypeRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class IssueType extends Entity {
  public function __construct() {
    parent::__construct();

    $this->issues = new ArrayCollection();
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
   *   targetEntity="AppBundle\Entity\Issue",
   *   mappedBy="type",
   *   fetch="EXTRA_LAZY"
   * )
   *
   * @JMS\Exclude()
   * @JMS\ReadOnly()
   */
  private $issues;

  /**
   * @var string
   *
   * @ORM\Column(name="name", type="string", length=100, nullable=false)
   *
   * @Assert\NotBlank()
   * @Assert\Length(max="100")
   */
  private $name;

  public function getFillable() {
    return array_merge(parent::getFillable(), array(
      'name'
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
   * Set name
   *
   * @param string $name
   *
   * @return Tag
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
   * Add issue
   *
   * @param \AppBundle\Entity\Issue $issue
   *
   * @return Tag
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
}
