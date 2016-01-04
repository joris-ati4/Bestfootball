<?php

namespace BF\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Duel
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="BF\SiteBundle\Entity\DuelRepository")
 */
class Duel
{
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
     * @ORM\Column(name="begin_date", type="datetime")
     */
    private $beginDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime")
     */
    private $endDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="accepted", type="boolean")
     */
    private $accepted;

    /**
     * @var integer
     *
     * @ORM\Column(name="host", type="integer")
     */
    private $host;

    /**
     * @var integer
     *
     * @ORM\Column(name="guest", type="integer")
     */
    private $guest;

    /**
     * @var boolean
     *
     * @ORM\Column(name="host_completed", type="boolean")
     */
    private $hostCompleted;

    /**
     * @var boolean
     *
     * @ORM\Column(name="guest_completed", type="boolean")
     */
    private $guestCompleted;

    /**
     * @var boolean
     *
     * @ORM\Column(name="completed", type="boolean")
     */
    private $completed;


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
     * Set beginDate
     *
     * @param \DateTime $beginDate
     *
     * @return Duel
     */
    public function setBeginDate($beginDate)
    {
        $this->beginDate = $beginDate;

        return $this;
    }

    /**
     * Get beginDate
     *
     * @return \DateTime
     */
    public function getBeginDate()
    {
        return $this->beginDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return Duel
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set accepted
     *
     * @param boolean $accepted
     *
     * @return Duel
     */
    public function setAccepted($accepted)
    {
        $this->accepted = $accepted;

        return $this;
    }

    /**
     * Get accepted
     *
     * @return boolean
     */
    public function getAccepted()
    {
        return $this->accepted;
    }

    /**
     * Set completed
     *
     * @param boolean $completed
     *
     * @return Duel
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;

        return $this;
    }

    /**
     * Get completed
     *
     * @return boolean
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     * Set hostCompleted
     *
     * @param boolean $hostCompleted
     *
     * @return Duel
     */
    public function setHostCompleted($hostCompleted)
    {
        $this->hostCompleted = $hostCompleted;

        return $this;
    }

    /**
     * Get hostCompleted
     *
     * @return boolean
     */
    public function getHostCompleted()
    {
        return $this->hostCompleted;
    }

    /**
     * Set guestCompleted
     *
     * @param boolean $guestCompleted
     *
     * @return Duel
     */
    public function setGuestCompleted($guestCompleted)
    {
        $this->guestCompleted = $guestCompleted;

        return $this;
    }

    /**
     * Get guestCompleted
     *
     * @return boolean
     */
    public function getGuestCompleted()
    {
        return $this->guestCompleted;
    }

    /**
     * Set host
     *
     * @param integer $host
     *
     * @return Duel
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host
     *
     * @return integer
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set guest
     *
     * @param integer $guest
     *
     * @return Duel
     */
    public function setGuest($guest)
    {
        $this->guest = $guest;

        return $this;
    }

    /**
     * Get guest
     *
     * @return integer
     */
    public function getGuest()
    {
        return $this->guest;
    }
}
