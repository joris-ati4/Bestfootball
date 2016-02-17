<?php

namespace BF\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Prediction
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="BF\SiteBundle\Entity\PredictionRepository")
 */
class Prediction
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
    * @ORM\ManyToOne(targetEntity="BF\SiteBundle\Entity\Challenge", inversedBy="videos")
    * @ORM\JoinColumn(nullable=false)
    */
    private $challenge;
    

    /**
     * @ORM\ManyToOne(targetEntity="BF\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_voter", referencedColumnName="id", nullable=false)
     * })
     */
    private $voter;

    /**
     *
     * @ORM\ManyToOne(targetEntity="BF\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_predictioned", referencedColumnName="id", nullable=false)
     * })
     */
    private $predictioned;






    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;


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
     * @return Prediction
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
     * Set challenge
     *
     * @param \BF\SiteBundle\Entity\Challenge $challenge
     *
     * @return Prediction
     */
    public function setChallenge(\BF\SiteBundle\Entity\Challenge $challenge)
    {
        $this->challenge = $challenge;

        return $this;
    }

    /**
     * Get challenge
     *
     * @return \BF\SiteBundle\Entity\Challenge
     */
    public function getChallenge()
    {
        return $this->challenge;
    }

    /**
     * Set voter
     *
     * @param \BF\UserBundle\Entity\User $voter
     *
     * @return Prediction
     */
    public function setVoter(\BF\UserBundle\Entity\User $voter)
    {
        $this->voter = $voter;

        return $this;
    }

    /**
     * Get voter
     *
     * @return \BF\UserBundle\Entity\User
     */
    public function getVoter()
    {
        return $this->voter;
    }

    /**
     * Set predictioner
     *
     * @param \BF\UserBundle\Entity\User $predictioner
     *
     * @return Prediction
     */
    public function setPredictioner(\BF\UserBundle\Entity\User $predictioner)
    {
        $this->predictioner = $predictioner;

        return $this;
    }

    /**
     * Get predictioner
     *
     * @return \BF\UserBundle\Entity\User
     */
    public function getPredictioner()
    {
        return $this->predictioner;
    }

    /**
     * Set predictioned
     *
     * @param \BF\UserBundle\Entity\User $predictioned
     *
     * @return Prediction
     */
    public function setPredictioned(\BF\UserBundle\Entity\User $predictioned)
    {
        $this->predictioned = $predictioned;

        return $this;
    }

    /**
     * Get predictioned
     *
     * @return \BF\UserBundle\Entity\User
     */
    public function getPredictioned()
    {
        return $this->predictioned;
    }
}
