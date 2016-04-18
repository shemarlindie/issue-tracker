<?php
/**
 * Created by PhpStorm.
 * User: shemarcl
 * Date: 4/5/16
 * Time: 5:06 PM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * User
 *
 * @ORM\Table(name="user_photos")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserPhotoRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class UserPhoto extends FileEntity {
  /**
   * @var integer
   *
   * @ORM\Column(name="id", type="bigint", unique=true)
   * @ORM\Id()
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var integer
   *
   * @ORM\Column(name="user_id", type="bigint", nullable=false)
   */
  private $userId;

  /**
   * @var User
   *
   * @ORM\OneToOne(
   *   targetEntity="AppBundle\Entity\User",
   *   inversedBy="photo"
   * )
   * @ORM\JoinColumn(
   *   name="user_id",
   *   referencedColumnName="id"
   * )
   *
   * @JMS\Exclude()
   */
  private $user;

  /**
   * @var string
   *
   * @ORM\Column(name="thumbnail_absolute_path", type="string", length=255, nullable=true)
   *
   * @JMS\Exclude()
   */
  private $thumbnailAbsolutePath;

  /**
   * @var string
   *
   * @ORM\Column(name="thumbnail_web_path", type="string", length=255, nullable=true)
   */
  private $thumbnailWebPath;

  /**
   * UserPhoto constructor.
   * @param User $user
   * @param UploadedFile|null $file
   */
  public function __construct(User $user, UploadedFile $file = NULL) {
    $this->setUser($user);
    parent::__construct($file);
  }

  /**
   * @ORM\PostPersist()
   * @ORM\PostUpdate()
   */
  public function upload() {
    if ($this->getFile() === NULL) {
      return FALSE;
    }

    parent::upload();
    $this->generateThumbnail();

    return FALSE;
  }

  /**
   * @ORM\PostRemove()
   */
  public function removeUpload() {
    parent::removeUpload();

    if (file_exists($this->thumbnailAbsolutePath)) {
      unlink($this->thumbnailAbsolutePath);
    }

    return FALSE;
  }

  protected function generateUniqueFileName(UploadedFile $file) {
    $filename = "{$this->getUser()->getId()}_{$this->uniqueString()}.{$file->guessExtension()}";

    return $filename;
  }

  private function uniqueString() {
    return sha1(uniqid(mt_rand(), TRUE));
  }

  /**
   * Sets file.
   *
   * @param UploadedFile $file
   */
  public function setFile(UploadedFile $file = NULL) {
    parent::setFile($file);

    if ($this->file && $this->isImage()) {
      // generate thumbnail file name
      $info = pathinfo($this->getAbsolutePath());
      $thumbnailPath = "{$info['dirname']}/{$info['filename']}_thumb.{$info['extension']}";

      $this->thumbnailAbsolutePath = $thumbnailPath;
      $this->thumbnailWebPath = $this->getUploadDir() . '/' .
        pathinfo($thumbnailPath, PATHINFO_BASENAME);
    }
  }

  /**
   * @return string
   */
  public function getUploadDir() {
    // get rid of the __DIR__ so it doesn't screw up
    // when displaying uploaded doc/image in the view.
    $dir = parent::getUploadDir();

    return "{$dir}/profile_photos";
  }

  /**
   * @param int $width
   * @return bool
   */
  public function generateThumbnail($width = 500) {
    if (!$this->isImage()) {
      return FALSE;
    }

    $image = new \Imagick($this->getAbsolutePath());
    $image->resizeImage($width, 0, \Imagick::FILTER_LANCZOS, 1);
    $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
    $image->setImageCompressionQuality(80);
    // Strip out unneeded meta data
    $image->stripImage();

    $image->writeImage($this->getThumbnailAbsolutePath());
    $image->destroy();

    return TRUE;
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
   * Set rentalId
   *
   * @param integer $userId
   *
   * @return UserPhoto
   */
  public function setUserId($userId) {
    $this->userId = $userId;

    return $this;
  }

  /**
   * Get rentalId
   *
   * @return integer
   */
  public function getUserId() {
    return $this->userId;
  }

  /**
   * Set user
   *
   * @param \AppBundle\Entity\User $user
   *
   * @return $this
   */
  public function setUser(User $user = NULL) {
    $this->user = $user;

    return $this;
  }

  /**
   * Get user
   *
   * @return \AppBundle\Entity\User
   */
  public function getUser() {
    return $this->user;
  }

  /**
   * Set thumbnailAbsolutePath
   *
   * @param string $thumbnailAbsolutePath
   *
   * @return UserPhoto
   */
  public function setThumbnailAbsolutePath($thumbnailAbsolutePath) {
    $this->thumbnailAbsolutePath = $thumbnailAbsolutePath;

    return $this;
  }

  /**
   * Get thumbnailAbsolutePath
   *
   * @return string
   */
  public function getThumbnailAbsolutePath() {
    return $this->thumbnailAbsolutePath;
  }

  /**
   * Set thumbnailWebPath
   *
   * @param string $thumbnailWebPath
   *
   * @return UserPhoto
   */
  public function setThumbnailWebPath($thumbnailWebPath) {
    $this->thumbnailWebPath = $thumbnailWebPath;

    return $this;
  }

  /**
   * Get thumbnailWebPath
   *
   * @return string
   */
  public function getThumbnailWebPath() {
    return $this->thumbnailWebPath;
  }
}
