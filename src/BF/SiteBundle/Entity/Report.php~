<?php

namespace BF\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Report
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="BF\SiteBundle\Entity\ReportRepository")
 */
class Report
{   
    /**
    * @ORM\ManyToOne(targetEntity="BF\SiteBundle\Entity\Video", inversedBy="reports")
    * @ORM\JoinColumn(nullable=false)
    */
    private $video;

    /**
    * @ORM\ManyToOne(targetEntity="BF\UserBundle\Entity\User", inversedBy="reports")
    * @ORM\JoinColumn(nullable=false)
    */
    private $user;


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="text")
     */
    private $reason;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text")
     */
    private $message;

    /**
     * @var string
     *
     * @ORM\Column(name="treated", type="boolean")
     */
    private $treated;


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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Report
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return Report
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set reason
     *
     * @param string $reason
     *
     * @return Report
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set video
     *
     * @param \BF\SiteBundle\Entity\Video $video
     *
     * @return Report
     */
    public function setVideo(\BF\SiteBundle\Entity\Video $video)
    {
        $this->video = $video;

        return $this;
    }

    /**
     * Get video
     *
     * @return \BF\SiteBundle\Entity\Video
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * Set user
     *
     * @param \BF\UserBundle\Entity\User $user
     *
     * @return Report
     */
    public function setUser(\BF\UserBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \BF\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set treated
     *
     * @param boolean $treated
     *
     * @return Report
     */
    public function setTreated($treated)
    {
        $this->treated = $treated;

        return $this;
    }

    /**
     * Get treated
     *
     * @return boolean
     */
    public function getTreated()
    {
        return $this->treated;
    }
}
