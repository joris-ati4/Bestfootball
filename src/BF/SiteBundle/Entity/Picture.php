<?php

namespace BF\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description: Picture
 * @ORM\Entity
 * @ORM\Table()
 * @ORM\HasLifecycleCallbacks
 */
class Picture {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="src", type="string", length=255)
     */
    private $src;

    /**
     * @var string
     *
     * @ORM\Column(name="alt", type="string", length=255)
     */
    private $alt;

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

    public function getUploadRootDir() {
        // absolute src to your directory where images must be saved
        return '/var/www/bestfootball.fr/shared/web/'.$this->getUploadDir();
    }

    public function getUploadDir() {
        return 'uploads/img';
    }

    public function getAbsolutePath() {
        return null === $this->src ? null : $this->getUploadRootDir() . '/' . $this->id . '.' . $this->src;
    }

    public function getWebPath() {
        return null === $this->alt ? null : '/' . $this->getUploadDir() . '/' . $this->alt;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload() {
        if (null !== $this->file) {
            // faites ce que vous voulez pour gÃ©nÃ©rer un nom unique
            $filename = sha1(uniqid(mt_rand(), true));
            $this->alt = $filename;
            $this->src = $filename . '.' . $this->file->guessExtension();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload() {
        if (null === $this->file) {
            return;
        }

        // s'il y a une erreur lors du dÃ©placement du fichier, une exception
        // va automatiquement Ãªtre lancÃ©e par la mÃ©thode move(). Cela va empÃªcher
        // proprement l'entitÃ© d'Ãªtre persistÃ©e dans la base de donnÃ©es si
        // erreur il y a
        $this->file->move($this->getUploadRootDir(), $this->src);

        unset($this->file);
    }

    /**
     * @ORM\PreRemove()
     */
    public function storeFilenameForRemove() {
        $this->filenameForRemove = $this->getAbsolutePath();
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload() {
        if ($this->filenameForRemove) {
            unlink($this->filenameForRemove);
        }
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set src
     *
     * @param string $src
     *
     * @return Picture
     */
    public function setSrc($src)
    {
        $this->src = $src;

        return $this;
    }

    /**
     * Get src
     *
     * @return string
     */
    public function getSrc()
    {
        return $this->src;
    }

    /**
     * Set alt
     *
     * @param string $alt
     *
     * @return Picture
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt
     *
     * @return string
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Picture
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Get file.
     *
     * @return $file
     */
    public function getFile() {
        return $this->file;
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
}
