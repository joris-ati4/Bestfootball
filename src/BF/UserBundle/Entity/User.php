<?php

namespace BF\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="BF\UserBundle\Entity\UserRepository")
 */
class User extends BaseUser
{
    /**
    * @ORM\OneToMany(targetEntity="BF\SiteBundle\Entity\Comment", mappedBy="user")
    */
    private $comments;

    /**
    * @ORM\OneToMany(targetEntity="BF\SiteBundle\Entity\Report", mappedBy="user")
    */
    private $reports; // Notez le « s », une annonce est liée à plusieurs candidatures

    /**
    * @ORM\ManyToMany(targetEntity="BF\SiteBundle\Entity\Duel", mappedBy="users")
    */
    private $duels; // Notez le « s », une annonce est liée à plusieurs candidatures

    /**
    * @ORM\ManyToOne(targetEntity="BF\SiteBundle\Entity\Country", inversedBy="users")
    * @ORM\JoinColumn(nullable=true)
    */
    private $country;

    /**
    * @ORM\ManyToOne(targetEntity="BF\SiteBundle\Entity\State", inversedBy="users")
    * @ORM\JoinColumn(nullable=true)
    */
    private $state;

    /**
    * @ORM\OneToOne(targetEntity="BF\SiteBundle\Entity\Media", cascade={"persist"})
    * @ORM\JoinColumn(nullable=false)
    */
    private $media;

    /**
    * @ORM\OneToMany(targetEntity="BF\SiteBundle\Entity\Video", mappedBy="user")
    */
    private $videos; // Notez le « s », une annonce est liée à plusieurs candidatures

    /**
    * @ORM\OneToMany(targetEntity="BF\SiteBundle\Entity\Notification", mappedBy="user")
    */
    private $notifications; // Notez le « s », une annonce est liée à plusieurs candidatures

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    //we add the collumns we want for the user

    /**
    * @ORM\Column(name="name", type="string", length=255, nullable=true)
    */
    private $name;

    /**
    * @ORM\Column(name="firstname", type="string", length=255, nullable=true)
    */
    private $firstname;

    /**
    * @ORM\Column(name="birthday", type="datetime", nullable=true)
    */
    private $birthday;

    /**
    * @ORM\Column(name="city", type="string", length=255, nullable=true)
    */
    private $city;

    /**
    * @ORM\Column(name="gender", type="string", length=10, nullable=true)
    */
    private $gender;

    /**
    * @ORM\Column(name="footballClub", type="string", length=255, nullable=true)
    */
    private $footballClub;

    /**
    * @ORM\Column(name="fieldPosition", type="string", length=255, nullable=true)
    */
    private $fieldPosition;

    /**
    * @ORM\Column(name="foot", type="string", length=255, nullable=true)
    */
    private $foot;

    /**
    * @ORM\Column(name="points", type="integer", length=100, nullable=true)
    */
    private $points;

    /**
    * @ORM\Column(name="duel_wins", type="integer", length=100, nullable=true)
    */
    private $duelWins;

    /**
    * @ORM\Column(name="duel_points", type="integer", length=100, nullable=true)
    */
    private $duelPoints;

    /** @ORM\Column(name="facebook_id", type="string", length=255, nullable=true) */
    protected $facebook_id;
    /** @ORM\Column(name="facebook_access_token", type="string", length=255, nullable=true) */
    protected $facebook_access_token;

    /** @ORM\Column(name="google_id", type="string", length=255, nullable=true) */
    protected $google_id;
    /** @ORM\Column(name="google_access_token", type="string", length=255, nullable=true) */
    protected $google_access_token;


    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
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
     * Set firstname
     *
     * @param string $firstname
     *
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set birthday
     *
     * @param \DateTime $birthday
     *
     * @return User
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return User
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return User
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set gender
     *
     * @param string $gender
     *
     * @return User
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set footballClub
     *
     * @param string $footballClub
     *
     * @return User
     */
    public function setFootballClub($footballClub)
    {
        $this->footballClub = $footballClub;

        return $this;
    }

    /**
     * Get footballClub
     *
     * @return string
     */
    public function getFootballClub()
    {
        return $this->footballClub;
    }

    /**
     * Set fieldPosition
     *
     * @param string $fieldPosition
     *
     * @return User
     */
    public function setFieldPosition($fieldPosition)
    {
        $this->fieldPosition = $fieldPosition;

        return $this;
    }

    /**
     * Get fieldPosition
     *
     * @return string
     */
    public function getFieldPosition()
    {
        return $this->fieldPosition;
    }

    /**
     * Set foot
     *
     * @param string $foot
     *
     * @return User
     */
    public function setFoot($foot)
    {
        $this->foot = $foot;

        return $this;
    }

    /**
     * Get foot
     *
     * @return string
     */
    public function getFoot()
    {
        return $this->foot;
    }

    /**
     * Add video
     *
     * @param \EZ\SiteBundle\Entity\Video $video
     *
     * @return User
     */
    public function addVideo(\BF\SiteBundle\Entity\Video $video)
    {
        $this->videos[] = $video;

        return $this;
    }

    /**
     * Remove video
     *
     * @param \EZ\SiteBundle\Entity\Video $video
     */
    public function removeVideo(\BF\SiteBundle\Entity\Video $video)
    {
        $this->videos->removeElement($video);
    }

    /**
     * Get videos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVideos()
    {
        return $this->videos;
    }

    /**
     * Set points
     *
     * @param integer $points
     *
     * @return User
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points
     *
     * @return integer
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set facebookId
     *
     * @param string $facebookId
     *
     * @return User
     */
    public function setFacebookId($facebookId)
    {
        $this->facebook_id = $facebookId;

        return $this;
    }

    /**
     * Get facebookId
     *
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebook_id;
    }

    /**
     * Set facebookAccessToken
     *
     * @param string $facebookAccessToken
     *
     * @return User
     */
    public function setFacebookAccessToken($facebookAccessToken)
    {
        $this->facebook_access_token = $facebookAccessToken;

        return $this;
    }

    /**
     * Get facebookAccessToken
     *
     * @return string
     */
    public function getFacebookAccessToken()
    {
        return $this->facebook_access_token;
    }

    /**
     * Set state
     *
     * @param \BF\SiteBundle\Entity\State $state
     *
     * @return User
     */
    public function setState(\BF\SiteBundle\Entity\State $state = null)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return \BF\SiteBundle\Entity\State
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Add duel
     *
     * @param \BF\SiteBundle\Entity\Duel $duel
     *
     * @return User
     */
    public function addDuel(\BF\SiteBundle\Entity\Duel $duel)
    {
        $this->duels[] = $duel;

        return $this;
    }

    /**
     * Remove duel
     *
     * @param \BF\SiteBundle\Entity\Duel $duel
     */
    public function removeDuel(\BF\SiteBundle\Entity\Duel $duel)
    {
        $this->duels->removeElement($duel);
    }

    /**
     * Get duels
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDuels()
    {
        return $this->duels;
    }

    /**
     * Add notification
     *
     * @param \BF\SiteBundle\Entity\Notification $notification
     *
     * @return User
     */
    public function addNotification(\BF\SiteBundle\Entity\Notification $notification)
    {
        $this->notifications[] = $notification;

        return $this;
    }

    /**
     * Remove notification
     *
     * @param \BF\SiteBundle\Entity\Notification $notification
     */
    public function removeNotification(\BF\SiteBundle\Entity\Notification $notification)
    {
        $this->notifications->removeElement($notification);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Add report
     *
     * @param \BF\SiteBundle\Entity\Report $report
     *
     * @return User
     */
    public function addReport(\BF\SiteBundle\Entity\Report $report)
    {
        $this->reports[] = $report;

        return $this;
    }

    /**
     * Remove report
     *
     * @param \BF\SiteBundle\Entity\Report $report
     */
    public function removeReport(\BF\SiteBundle\Entity\Report $report)
    {
        $this->reports->removeElement($report);
    }

    /**
     * Get reports
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * Set duelPoints
     *
     * @param integer $duelPoints
     *
     * @return User
     */
    public function setDuelPoints($duelPoints)
    {
        $this->duelPoints = $duelPoints;

        return $this;
    }

    /**
     * Get duelPoints
     *
     * @return integer
     */
    public function getDuelPoints()
    {
        return $this->duelPoints;
    }

    /**
     * Set googleId
     *
     * @param string $googleId
     *
     * @return User
     */
    public function setGoogleId($googleId)
    {
        $this->google_id = $googleId;

        return $this;
    }

    /**
     * Get googleId
     *
     * @return string
     */
    public function getGoogleId()
    {
        return $this->google_id;
    }

    /**
     * Set googleAccessToken
     *
     * @param string $googleAccessToken
     *
     * @return User
     */
    public function setGoogleAccessToken($googleAccessToken)
    {
        $this->google_access_token = $googleAccessToken;

        return $this;
    }

    /**
     * Get googleAccessToken
     *
     * @return string
     */
    public function getGoogleAccessToken()
    {
        return $this->google_access_token;
    }

    /**
     * Set duelWins
     *
     * @param integer $duelWins
     *
     * @return User
     */
    public function setDuelWins($duelWins)
    {
        $this->duelWins = $duelWins;

        return $this;
    }

    /**
     * Get duelWins
     *
     * @return integer
     */
    public function getDuelWins()
    {
        return $this->duelWins;
    }

    /**
     * Set media
     *
     * @param \BF\SiteBundle\Entity\Media $media
     *
     * @return User
     */
    public function setMedia(\BF\SiteBundle\Entity\Media $media)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * Get media
     *
     * @return \BF\SiteBundle\Entity\Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Add comment
     *
     * @param \BF\SiteBundle\Entity\Comment $comment
     *
     * @return User
     */
    public function addComment(\BF\SiteBundle\Entity\Comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \BF\SiteBundle\Entity\Comment $comment
     */
    public function removeComment(\BF\SiteBundle\Entity\Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }
}
