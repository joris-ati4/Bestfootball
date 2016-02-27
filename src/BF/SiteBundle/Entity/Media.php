<?php

namespace BF\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description: Media
 * @todo adjust to your need if you want to handle uploads by lifecyclecallback
 * @ORM\Entity
 * @ORM\Table()
 * @ORM\HasLifecycleCallbacks <-
 */
class Media {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $image;



    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;

    public function setPath($path) {
        $this->path = $path;

        return $this;
    }

    public function getPath() {
        return $this->path;
    }

    /**
     * @ORM\Column(name="created",type="date")
     */
    protected $created;

    /**
     * @var File
     *
     * @Assert\File(
     *     maxSize = "1M",
     *     mimeTypes = {"image/jpeg"},
     *     maxSizeMessage = "The maxmimum allowed file size is 5MB.",
     *     mimeTypesMessage = "Only the filetypes image are allowed."
     * )
     */
    protected $file;

    public function __construct() {
        $this->created = new \Datetime();
    }


    public function getUploadRootDir()
    {
        // absolute path to your directory where images must be saved
        return '/var/www/bestfootball.fr/shared/web/'.$this->getUploadDir();
    }

    public function getUploadDir()
    {
        return 'uploads/img';
    }

    public function getAbsolutePath()
    {
        return null === $this->image ? null : $this->getUploadRootDir().'/'.$this->image;
    }

    public function getWebPath()
    {
        return null === $this->image ? null : '/'.$this->getUploadDir().'/'.$this->image;
    }


    /**
     * Get id.
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return Media
     */
    public function setCreated($created) {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
    }

    /* Set file
     *
     * @param $file
     * @return Media
     */

    public function setFile($file) {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file.
     *
     * @return $file
     */
    public function getFile() {
        return $this->file;
    }


    /**
     * Set image
     *
     * @param string $image
     *
     * @return Media
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }
}
