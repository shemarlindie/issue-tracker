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
 * Tag
 *
 * @ORM\Table(name="tags")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TagRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class Tag extends Entity {
  public function __construct() {
    parent::__construct();

    $this->projects = new ArrayCollection();
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
   * @ORM\ManyToMany(
   *   targetEntity="AppBundle\Entity\Project",
   *   mappedBy="tags",
   *   fetch="EXTRA_LAZY"
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
   *   targetEntity="AppBundle\Entity\Issue",
   *   mappedBy="tags",
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
   * Add project
   *
   * @param \AppBundle\Entity\Project $project
   *
   * @return Tag
   */
  public function addProject(\AppBundle\Entity\Project $project) {
    $this->projects[] = $project;

    return $this;
  }

  /**
   * Remove project
   *
   * @param \AppBundle\Entity\Project $project
   */
  public function removeProject(\AppBundle\Entity\Project $project) {
    $this->projects->removeElement($project);
  }

  /**
   * Get projects
   *
   * @return \Doctrine\Common\Collections\Collection
   */
  public function getProjects() {
    return $this->projects;
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
