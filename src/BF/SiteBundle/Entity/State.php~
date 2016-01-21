<?php

namespace BF\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * State
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="BF\SiteBundle\Entity\StateRepository")
 */
class State
{
    /**
    * @ORM\OneToMany(targetEntity="BF\UserBundle\Entity\User", mappedBy="state")
    */
    private $users; // Notez le « s », une annonce est liée à plusieurs candidatures

    /**
    * @ORM\ManyToOne(targetEntity="BF\SiteBundle\Entity\Country", inversedBy="states")
    * @ORM\JoinColumn(nullable=false)
    */
    private $country;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


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
     * Set name
     *
     * @param string $name
     *
     * @return State
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add user
     *
     * @param \BF\UserBundle\Entity\User $user
     *
     * @return State
     */
    public function addUser(\BF\UserBundle\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \BF\UserBundle\Entity\User $user
     */
    public function removeUser(\BF\UserBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set country
     *
     * @param \BF\SiteBundle\Entity\Country $country
     *
     * @return State
     */
    public function setCountry(\BF\SiteBundle\Entity\Country $country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return \BF\SiteBundle\Entity\Country
     */
    public function getCountry()
    {
        return $this->country;
    }
}
