<?php
/**
 * Created by PhpStorm.
 * User: shemarcl
 * Date: 3/12/16
 * Time: 1:35 AM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @ORM\HasLifecycleCallbacks()
 */
abstract class Entity {
  /**
   * @var array
   *
   * Used to mass assign an array of data to fields in the Entity class.
   * e.g. array(
   *  'fieldName' => 'field_name'
   * )
   * Maps the fieldName field to the value of (field_name|fieldName) in an array.
   *
   * Used by the fill() method.
   *
   * @JMS\Exclude()
   *
   */
  protected $fillable = array();

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="date_created", type="datetime", nullable=false)
   */
  protected $dateCreated;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="date_updated", type="datetime", nullable=false)
   */
  protected $dateUpdated;

  /**
   * Entity constructor.
   */
  public function __construct() {
  }

  /**
   * @return array
   */
  public function getFillable() {
    return $this->fillable;
  }

  /**
   * @ORM\PrePersist()
   */
  public function prePersist() {
    $now = new \DateTime();

    $this
      ->setDateUpdated($now)
      ->setDateCreated($now);
  }

  /**
   * @ORM\PreUpdate()
   */
  public function preUpdate() {
    $now = new \DateTime();

    $this->setDateUpdated($now);
  }

  /**
   * Updates fillable properties with $data.
   *
   * $only and $include follows the same assoc array pattern as $this->getFillable().
   * $exclude should be a simple array of fillable fields to exclude from mass assignment.
   *
   * @param array $only
   * map only fields from this array.
   *
   * @param array $include
   * map $this->getFillable() along with $fields from this array.
   *
   * @param array $exclude
   * map all fields in $this->getFillable() EXCEPT those defined in this array.
   *
   * @param array $data
   * an assoc array of values to map.
   *
   * @return $this
   *
   */
  public function fill($data = array(), $only = null, $include = null, $exclude = null) {
    $fillable = is_array($only) ? $only :
      is_array($include) ? array_merge($this->getFillable(), $include) :
        is_array($exclude) ? array_diff_key($this->getFillable(), array_flip($exclude)) :
          $this->getFillable();

    foreach ($fillable as $field => $mapping) {
      if (is_numeric($field)) $field = $mapping; // simple array item; field and mapping are the same

      if (array_key_exists($mapping, $data)) {
        $key = $mapping;
        $setter = 'set' . ucfirst($field);

        if (method_exists($this, $setter)) {
          $this->{$setter}($data[$key]);
        } else {
          throw new Exception("There is no setter for fillable property '{$field}'.");
        }
      }
    }

    return $this;
  }

  /**
   * @return \DateTime
   */
  public function getDateCreated() {
    return $this->dateCreated;
  }

  /**
   * @param \DateTime $dateCreated
   * @return Entity
   */
  public function setDateCreated($dateCreated) {
    $this->dateCreated = $dateCreated;
    return $this;
  }

  /**
   * @return \DateTime
   */
  public function getDateUpdated() {
    return $this->dateUpdated;
  }

  /**
   * @param \DateTime $dateUpdated
   * @return Entity
   */
  public function setDateUpdated($dateUpdated) {
    $this->dateUpdated = $dateUpdated;
    return $this;
  }
}