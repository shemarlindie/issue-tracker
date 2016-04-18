<?php
/**
 * Created by PhpStorm.
 * User: shemarcl
 * Date: 4/5/16
 * Time: 6:28 AM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

abstract class FileEntity extends Entity {
  const FILENAME_PATTERN = '/[^a-zA-Z0-9_\-\. ]/';

  /**
   * @var UploadedFile
   *
   * @Assert\File(
   *   maxSize="524288000",
   *   mimeTypes={
   *   "image/jpeg", "image/png", "image/gif",
   *   "video/*",
   *   "application/pdf", "text/plain",
   *   "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
   *   "application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
   *   "application/vnd.ms-powerpoint", "application/vnd.openxmlformats-officedocument.presentationml.presentation"
   * }
   *   )
   */
  protected $file;

  /**
   * @var string
   */
  protected $temp;

  /**
   * @var string
   *
   * @ORM\Column(name="name", type="string", length=255, nullable=false)
   * @Assert\NotBlank()
   */
  protected $name;

  /**
   * @var string
   *
   * @ORM\Column(name="original_name", type="string", length=255, nullable=true)
   */
  protected $originalName;

  /**
   * @var string
   *
   * @ORM\Column(name="caption", type="string", length=100, nullable=true)
   *
   * @Assert\Length(max="100", groups={"metadata", "Default"})
   */
  protected $caption;

  /**
   * @var string
   *
   * @ORM\Column(name="path", type="string", length=255, nullable=false)
   * @Assert\NotBlank()
   */
  protected $path;

  /**
   * @var integer
   *
   * @ORM\Column(name="size", type="bigint", nullable=true)
   *
   * @JMS\Type("integer")
   */
  protected $size;

  /**
   * @var string
   *
   * @ORM\Column(name="mime_type", type="string", length=255, nullable=true)
   */
  protected $mimeType;


  /**
   * RentalAttachment constructor.
   * @param UploadedFile|null $file
   */
  public function __construct(UploadedFile $file = NULL) {
    parent::__construct();
    $this->setFile($file);
  }

  public function getFillable() {
    return array_merge(parent::getFillable(), array(
      'caption' => 'caption'
    ));
  }


  /**
   * @ORM\PostPersist()
   * @ORM\PostUpdate()
   */
  public function upload() {
    if ($this->getFile() === NULL) {
      return;
    }

    // if there is an error when moving the file, an exception will
    // be automatically thrown by move(). This will properly prevent
    // the entity from being persisted to the database on error
    $this->getFile()
      ->move($this->getUploadRootDir() . '/' . dirname($this->getPath()), $this->getName());

    // check if we have an old file
    if (isset($this->temp)) {
      // delete the old file
      unlink($this->getUploadRootDir() . '/' . $this->temp);
      // clear the temp file path
      $this->temp = NULL;
    }
    $this->file = NULL;
  }

  /**
   * @ORM\PostRemove()
   */
  public function removeUpload() {
    $file = $this->getAbsolutePath();
    if (file_exists($file)) {
      unlink($file);
    }
  }

  protected function generateUniqueFileName(UploadedFile $file) {
    $filename = sha1(uniqid(mt_rand(), TRUE)) . '.' . $file->guessExtension();

    return $filename;
  }

  /**
   * Should be overridden by subclasses to save uploads in a different folder.
   *
   * @param $filename
   * @return mixed
   */
  protected function generatePath($filename) {

    return $filename;
  }

  /**
   * Sets file.
   *
   * @param UploadedFile $file
   */
  public function setFile(UploadedFile $file = NULL) {
    $this->file = $file;

    // check if we have an old file path
    if (isset($this->path)) {
      // store the old name to delete after the update
      $this->temp = $this->path;
      $this->path = NULL;
    }

    if ($this->file) {
      $this->setOriginalName($file->getClientOriginalName());
      $this->setSize($file->getSize());
      $this->setMimeType($file->getMimeType());

      // generate unique file name
      $this->name = $this->generateUniqueFileName($file);
      $this->path = $this->generatePath($this->name);
    }
  }

  /**
   * @return UploadedFile
   */
  public function getFile() {
    return $this->file;
  }

  /**
   * @return null|string
   */
  public function getAbsolutePath() {
    return $this->path === NULL ? NULL :
      $this->getUploadRootDir() . '/' . $this->path;
  }

  /**
   * @return null|string
   *
   * @JMS\VirtualProperty()
   */
  public function getWebPath() {
    return $this->path === NULL ? NULL :
      $this->getUploadDir() . '/' . $this->path;
  }

  /**
   * @return string
   */
  public function getUploadRootDir() {
    // the absolute directory path where uploaded
    // documents should be saved
    return __DIR__ . '/../../../web/' . $this->getUploadDir();
  }

  /**
   * Should be overridden by subclasses to set a upload directory for that specific
   * upload type.
   *
   * @return string
   */
  public function getUploadDir() {
    // get rid of the __DIR__ so it doesn't screw up
    // when displaying uploaded doc/image in the view.
    return 'site_data/uploads';
  }

  /**
   * @return mixed
   *
   * @JMS\VirtualProperty()
   */
  public function getExtension() {
    return pathinfo($this->getName(), PATHINFO_EXTENSION);
  }

  /**
   * @return bool
   *
   * @JMS\VirtualProperty()
   */
  public function isImage() {
    return strncmp(strtolower($this->getMimeType()), 'image/', 6) == 0;
  }

  /**
   * @return bool
   *
   * @JMS\VirtualProperty()
   */
  public function isVideo() {
    return strncmp(strtolower($this->getMimeType()), 'video/', 6) == 0;
  }

  /**
   * @return bool
   *
   * @JMS\VirtualProperty()
   */
  public function isAudio() {
    return strncmp(strtolower($this->getMimeType()), 'audio/', 6) == 0;
  }

  /**
   * Set name
   *
   * @param string $name
   *
   * @return FileEntity
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
   * Set originalName
   *
   * @param string $originalName
   *
   * @return FileEntity
   */
  public function setOriginalName($originalName) {
    $decoded = urldecode($originalName);
    $this->originalName = preg_replace($this::FILENAME_PATTERN, '_', $decoded);

    return $this;
  }

  /**
   * Get originalName
   *
   * @return string
   */
  public function getOriginalName() {
    return $this->originalName;
  }

  /**
   * Set path
   *
   * @param string $path
   *
   * @return FileEntity
   */
  public function setPath($path) {
    $this->path = $path;

    return $this;
  }

  /**
   * Get path
   *
   * @return string
   */
  public function getPath() {
    return $this->path;
  }

  /**
   * Set size
   *
   * @param integer $size
   *
   * @return FileEntity
   */
  public function setSize($size) {
    $this->size = $size;

    return $this;
  }

  /**
   * Get size
   *
   * @return integer
   */
  public function getSize() {
    return $this->size;
  }

  /**
   * Set mimeType
   *
   * @param string $mimeType
   *
   * @return FileEntity
   */
  public function setMimeType($mimeType) {
    $this->mimeType = $mimeType;

    return $this;
  }

  /**
   * Get mimeType
   *
   * @return string
   */
  public function getMimeType() {
    return $this->mimeType;
  }

  /**
   * Set caption
   *
   * @param string $caption
   *
   * @return FileEntity
   */
  public function setCaption($caption) {
    $this->caption = $caption;

    return $this;
  }

  /**
   * Get caption
   *
   * @return string
   */
  public function getCaption() {
    return $this->caption;
  }
}