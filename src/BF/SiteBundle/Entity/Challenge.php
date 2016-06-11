<?php

namespace BF\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\TimeCode;

/**
 * Challenge
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="BF\SiteBundle\Entity\ChallengeRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Challenge
{
    /**
    * @ORM\OneToMany(targetEntity="BF\SiteBundle\Entity\Video", mappedBy="challenge")
    */
    private $videos; // Notez le « s », une annonce est liée à plusieurs candidatures

    /**
    * @ORM\OneToMany(targetEntity="BF\SiteBundle\Entity\Duel", mappedBy="challenge")
    */
    private $duels; // Notez le « s », une annonce est liée à plusieurs candidatures

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
    * @Gedmo\Slug(fields={"titleFR"})
    * @ORM\Column(length=128, unique=true)
    */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="title_fr", type="string", length=255)
     */
    private $titleFR;

    /**
     * @var string
     *
     * @ORM\Column(name="title_en", type="string", length=255)
     */
    private $titleEN;

    /**
     *
     * @ORM\Column(name="description_fr", type="text")
     */
    private $descriptionFR;

    /**
     *
     * @ORM\Column(name="description_en", type="text")
     */
    private $descriptionEN;

    /**
     * @var string
     *
     * @ORM\Column(name="video_url", type="string", length=255, nullable=true)
     */
    private $videoUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="image_url", type="string", length=255, nullable=true)
     */
    private $imageUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="image_alt", type="string", length=255, nullable=true)
     */
    private $imageAlt;

    

    /**
     * @var string
     *
     * @ORM\Column(name="one", type="integer", length=255)
     */
    private $one;

    /**
     * @var string
     *
     * @ORM\Column(name="two", type="integer", length=255)
     */
    private $two;
    /**
     * @var string
     *
     * @ORM\Column(name="three", type="integer", length=255)
     */
    private $three;

    /**
     * @var string
     *
     * @ORM\Column(name="four", type="integer", length=255)
     */
    private $four;

    /**
     * @var string
     *
     * @ORM\Column(name="five", type="integer", length=255)
     */
    private $five;

    /**
     * @var string
     *
     * @ORM\Column(name="six", type="integer", length=255)
     */
    private $six;

    /**
    * @ORM\Column(name="date", type="datetime")
    */
    private $date;

    /**
    * @ORM\Column(name="endDate", type="datetime")
    */
    private $endDate;

    /**
    * @ORM\Column(name="partner", type="boolean")
    */
    private $partner;

    /**
    * @ORM\Column(name="logo_partner", type="string", length=255, nullable=true)
    */
    private $logoPartner;

    /**
    * @ORM\Column(name="nom_partner", type="string", length=255, nullable=true)
    */
    private $nomPartner;

    /**
    * @ORM\Column(name="lien_partner", type="string", length=255, nullable=true)
    */
    private $lienPartner; 

    /**
    * @ORM\Column(name="description_partner_EN", type="text", nullable=true)
    */
    private $descriptionPartnerEN;

    /**
    * @ORM\Column(name="description_partner_FR", type="text", nullable=true)
    */
    private $descriptionPartnerFR;

    /**
    * @ORM\Column(name="first_prize_title_EN", type="string", length=255, nullable=true)
    */
    private $firstPrizeTitleEN;

    /**
    * @ORM\Column(name="first_prize_title_FR", type="string", length=255, nullable=true)
    */
    private $firstPrizeTitleFR;

    /**
    * @ORM\Column(name="first_prize_img", type="string", length=255, nullable=true)
    */
    private $firstPrizeLogo; 

    /**
    * @ORM\Column(name="second_prize_title_EN", type="string", length=255, nullable=true)
    */
    private $secondPrizeTitleEN;

    /**
    * @ORM\Column(name="second_prize_title_FR", type="string", length=255, nullable=true)
    */
    private $secondPrizeTitleFR;

    /**
    * @ORM\Column(name="second_prize_img", type="string", length=255, nullable=true)
    */
    private $secondPrizeLogo;

    /**
    * @ORM\Column(name="third_prize_title_EN", type="string", length=255, nullable=true)
    */
    private $thirdPrizeTitleEN;

    /**
    * @ORM\Column(name="third_prize_title_FR", type="string", length=255, nullable=true)
    */
    private $thirdPrizeTitleFR;

    /**
    * @ORM\Column(name="third_prize_img", type="string", length=255, nullable=true)
    */
    private $thirdPrizeLogo; 

    /**
    * @ORM\Column(name="type", type="string", length=255)
    */
    private $type;

    /**
    * @ORM\Column(name="active", type="boolean")
    */
    private $active;

     private $file;

    // On ajoute cet attribut pour y stocker le nom du fichier temporairement
    private $tempFilename;
    private $extension;


    public function getFile()
    {
      return $this->file;
    }

    // On modifie le setter de File, pour prendre en compte l'upload d'un fichier lorsqu'il en existe déjà un autre
    public function setFile(UploadedFile $file)
    {
      $this->file = $file;

      // On vérifie si on avait déjà un fichier pour cette entité
      if (null !== $this->videoUrl) {
        // On sauvegarde l'extension du fichier pour le supprimer plus tard
        $this->tempFilename = $this->videoUrl;
        // On réinitialise les valeurs des attributs url et alt
        $this->videoUrl = null;
        $this->imageUrl = null;
        $this->imageAlt = null;
        }
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
      // Si jamais il n'y a pas de fichier (champ facultatif)
      if (null === $this->file) {
        return;
      }

      // Le nom du fichier est son id, on doit juste stocker également son extension
      // Pour faire propre, on devrait renommer cet attribut en « extension », plutôt que « url »
      $this->videoUrl = 'mp4';
      $this->extension = $this->file->guessExtension();
      // Et on génère l'attribut alt de la balise <img>, à la valeur du nom du fichier sur le PC de l'internaute
      $this->imageAlt = $this->file->getClientOriginalName();
      $this->imageUrl = 'jpg';
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
      // Si jamais il n'y a pas de fichier (champ facultatif)
      if (null === $this->file) {
        return;
      }

      // Si on avait un ancien fichier, on le supprime
      if (null !== $this->tempFilename) {
        $oldFile = $this->getUploadRootDir().'/'.$this->id.'.'.$this->tempFilename;
        if (file_exists($oldFile)) {
          unlink($oldFile);
        }
      }

      // On déplace le fichier envoyé dans le répertoire de notre choix
      $this->file->move(
        $this->getUploadRootDir(), // Le répertoire de destination
        $this->id.'.'.$this->extension  // Le nom du fichier à créer, ici « id.extension »
      );

      //here we will convert the video to mp4 and webm and save the thumbnail.

      //the file is stocked in /Foot/web/uploads/videos/ + filename

      //this is the code to convert the file we receive into webm and mp4. We need to change to accept all file sizes.
        $ffmpeg = FFMpeg::create(array(
            'ffmpeg.binaries'  => '/home/joris/bin/ffmpeg',
            'ffprobe.binaries' => '/home/joris/bin/ffprobe',
            'timeout'          => 3600, // The timeout for the underlying process
            'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use
        ));

        // Open video
        $video = $ffmpeg->open($this->getUploadRootDir().'/'.$this->id.'.'.$this->extension);

        $video
            ->frame( TimeCode::fromSeconds(1))
            ->save('/var/www/bestfootball.fr/shared/web/uploads/challenges/thumbnail/'.$this->id.'.jpg');
        // Resize to 1280x720 to compact the video ! 
        $video
            ->filters()
            ->resize(new Dimension(1280, 720), ResizeFilter::RESIZEMODE_INSET)
            ->synchronize();

        // Start transcoding and save video
            if($this->extension != 'mp4')
            {
                $video->save(new X264(),'/var/www/bestfootball.fr/shared/web/uploads/challenges/'.$this->id.'.mp4');
                unlink($this->getUploadRootDir().'/'.$this->id.'.'.$this->extension);
            }
       
    }

    /**
     * @ORM\PreRemove()
     */
    public function preRemoveUpload()
    {
      // On sauvegarde temporairement le nom du fichier, car il dépend de l'id
      $this->tempFilename = $this->getUploadRootDir().'/'.$this->id.'.'.$this->videoUrl;
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
      // En PostRemove, on n'a pas accès à l'id, on utilise notre nom sauvegardé
      if (file_exists($this->tempFilename)) {
        // On supprime le fichier
        unlink($this->tempFilename);
      }
    }

    public function getUploadDir()
    {
      // On retourne le chemin relatif vers l'image pour un navigateur
      return 'uploads/challenges';
    }

    protected function getUploadRootDir()
    {
      // On retourne le chemin relatif vers l'image pour notre code PHP
      return '/var/www/bestfootball.fr/shared/web/'.$this->getUploadDir();
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
     * Set title
     *
     * @param string $title
     *
     * @return Challenge
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set videoUrl
     *
     * @param string $videoUrl
     *
     * @return Challenge
     */
    public function setVideoUrl($videoUrl)
    {
        $this->videoUrl = $videoUrl;

        return $this;
    }

    /**
     * Get videoUrl
     *
     * @return string
     */
    public function getVideoUrl()
    {
        return $this->videoUrl;
    }

    /**
     * Set imageUrl
     *
     * @param string $imageUrl
     *
     * @return Challenge
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * Get imageUrl
     *
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * Set imageAlt
     *
     * @param string $imageAlt
     *
     * @return Challenge
     */
    public function setImageAlt($imageAlt)
    {
        $this->imageAlt = $imageAlt;

        return $this;
    }

    /**
     * Get imageAlt
     *
     * @return string
     */
    public function getImageAlt()
    {
        return $this->imageAlt;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->date = new \Datetime();
        $this->videos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add video
     *
     * @param \BF\SiteBundle\Entity\Video $video
     *
     * @return Challenge
     */
    public function addVideo(\BF\SiteBundle\Entity\Video $video)
    {
        $this->videos[] = $video;

        return $this;
    }

    /**
     * Remove video
     *
     * @param \BF\SiteBundle\Entity\Video $video
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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Challenge
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
     * Set partner
     *
     * @param boolean $partner
     *
     * @return Challenge
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     * Get partner
     *
     * @return boolean
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * Add duel
     *
     * @param \BF\SiteBundle\Entity\Duel $duel
     *
     * @return Challenge
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
     * Set one
     *
     * @param integer $one
     *
     * @return Challenge
     */
    public function setOne($one)
    {
        $this->one = $one;

        return $this;
    }

    /**
     * Get one
     *
     * @return integer
     */
    public function getOne()
    {
        return $this->one;
    }

    /**
     * Set two
     *
     * @param integer $two
     *
     * @return Challenge
     */
    public function setTwo($two)
    {
        $this->two = $two;

        return $this;
    }

    /**
     * Get two
     *
     * @return integer
     */
    public function getTwo()
    {
        return $this->two;
    }

    /**
     * Set three
     *
     * @param integer $three
     *
     * @return Challenge
     */
    public function setThree($three)
    {
        $this->three = $three;

        return $this;
    }

    /**
     * Get three
     *
     * @return integer
     */
    public function getThree()
    {
        return $this->three;
    }

    /**
     * Set four
     *
     * @param integer $four
     *
     * @return Challenge
     */
    public function setFour($four)
    {
        $this->four = $four;

        return $this;
    }

    /**
     * Get four
     *
     * @return integer
     */
    public function getFour()
    {
        return $this->four;
    }

    /**
     * Set five
     *
     * @param integer $five
     *
     * @return Challenge
     */
    public function setFive($five)
    {
        $this->five = $five;

        return $this;
    }

    /**
     * Get five
     *
     * @return integer
     */
    public function getFive()
    {
        return $this->five;
    }

    /**
     * Set six
     *
     * @param integer $six
     *
     * @return Challenge
     */
    public function setSix($six)
    {
        $this->six = $six;

        return $this;
    }

    /**
     * Get six
     *
     * @return integer
     */
    public function getSix()
    {
        return $this->six;
    }

    /**
     * Set descriptionFR
     *
     * @param string $descriptionFR
     *
     * @return Challenge
     */
    public function setDescriptionFR($descriptionFR)
    {
        $this->descriptionFR = $descriptionFR;

        return $this;
    }

    /**
     * Get descriptionFR
     *
     * @return string
     */
    public function getDescriptionFR()
    {
        return $this->descriptionFR;
    }

    /**
     * Set descriptionEN
     *
     * @param string $descriptionEN
     *
     * @return Challenge
     */
    public function setDescriptionEN($descriptionEN)
    {
        $this->descriptionEN = $descriptionEN;

        return $this;
    }

    /**
     * Get descriptionEN
     *
     * @return string
     */
    public function getDescriptionEN()
    {
        return $this->descriptionEN;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Challenge
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set titleFR
     *
     * @param string $titleFR
     *
     * @return Challenge
     */
    public function setTitleFR($titleFR)
    {
        $this->titleFR = $titleFR;

        return $this;
    }

    /**
     * Get titleFR
     *
     * @return string
     */
    public function getTitleFR()
    {
        return $this->titleFR;
    }

    /**
     * Set titleEN
     *
     * @param string $titleEN
     *
     * @return Challenge
     */
    public function setTitleEN($titleEN)
    {
        $this->titleEN = $titleEN;

        return $this;
    }

    /**
     * Get titleEN
     *
     * @return string
     */
    public function getTitleEN()
    {
        return $this->titleEN;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Challenge
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return Challenge
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
     * Set logoPartner
     *
     * @param string $logoPartner
     *
     * @return Challenge
     */
    public function setLogoPartner($logoPartner)
    {
        $this->logoPartner = $logoPartner;

        return $this;
    }

    /**
     * Get logoPartner
     *
     * @return string
     */
    public function getLogoPartner()
    {
        return $this->logoPartner;
    }

    /**
     * Set nomPartner
     *
     * @param string $nomPartner
     *
     * @return Challenge
     */
    public function setNomPartner($nomPartner)
    {
        $this->nomPartner = $nomPartner;

        return $this;
    }

    /**
     * Get nomPartner
     *
     * @return string
     */
    public function getNomPartner()
    {
        return $this->nomPartner;
    }

    /**
     * Set lienPartner
     *
     * @param string $lienPartner
     *
     * @return Challenge
     */
    public function setLienPartner($lienPartner)
    {
        $this->lienPartner = $lienPartner;

        return $this;
    }

    /**
     * Get lienPartner
     *
     * @return string
     */
    public function getLienPartner()
    {
        return $this->lienPartner;
    }

    /**
     * Set descriptionPartnerEN
     *
     * @param string $descriptionPartnerEN
     *
     * @return Challenge
     */
    public function setDescriptionPartnerEN($descriptionPartnerEN)
    {
        $this->descriptionPartnerEN = $descriptionPartnerEN;

        return $this;
    }

    /**
     * Get descriptionPartnerEN
     *
     * @return string
     */
    public function getDescriptionPartnerEN()
    {
        return $this->descriptionPartnerEN;
    }

    /**
     * Set descriptionPartnerFR
     *
     * @param string $descriptionPartnerFR
     *
     * @return Challenge
     */
    public function setDescriptionPartnerFR($descriptionPartnerFR)
    {
        $this->descriptionPartnerFR = $descriptionPartnerFR;

        return $this;
    }

    /**
     * Get descriptionPartnerFR
     *
     * @return string
     */
    public function getDescriptionPartnerFR()
    {
        return $this->descriptionPartnerFR;
    }

    /**
     * Set firstPrizeTitleEN
     *
     * @param string $firstPrizeTitleEN
     *
     * @return Challenge
     */
    public function setFirstPrizeTitleEN($firstPrizeTitleEN)
    {
        $this->firstPrizeTitleEN = $firstPrizeTitleEN;

        return $this;
    }

    /**
     * Get firstPrizeTitleEN
     *
     * @return string
     */
    public function getFirstPrizeTitleEN()
    {
        return $this->firstPrizeTitleEN;
    }

    /**
     * Set firstPrizeTitleFR
     *
     * @param string $firstPrizeTitleFR
     *
     * @return Challenge
     */
    public function setFirstPrizeTitleFR($firstPrizeTitleFR)
    {
        $this->firstPrizeTitleFR = $firstPrizeTitleFR;

        return $this;
    }

    /**
     * Get firstPrizeTitleFR
     *
     * @return string
     */
    public function getFirstPrizeTitleFR()
    {
        return $this->firstPrizeTitleFR;
    }

    /**
     * Set firstPrizeLogo
     *
     * @param string $firstPrizeLogo
     *
     * @return Challenge
     */
    public function setFirstPrizeLogo($firstPrizeLogo)
    {
        $this->firstPrizeLogo = $firstPrizeLogo;

        return $this;
    }

    /**
     * Get firstPrizeLogo
     *
     * @return string
     */
    public function getFirstPrizeLogo()
    {
        return $this->firstPrizeLogo;
    }

    /**
     * Set secondPrizeTitleEN
     *
     * @param string $secondPrizeTitleEN
     *
     * @return Challenge
     */
    public function setSecondPrizeTitleEN($secondPrizeTitleEN)
    {
        $this->secondPrizeTitleEN = $secondPrizeTitleEN;

        return $this;
    }

    /**
     * Get secondPrizeTitleEN
     *
     * @return string
     */
    public function getSecondPrizeTitleEN()
    {
        return $this->secondPrizeTitleEN;
    }

    /**
     * Set secondPrizeTitleFR
     *
     * @param string $secondPrizeTitleFR
     *
     * @return Challenge
     */
    public function setSecondPrizeTitleFR($secondPrizeTitleFR)
    {
        $this->secondPrizeTitleFR = $secondPrizeTitleFR;

        return $this;
    }

    /**
     * Get secondPrizeTitleFR
     *
     * @return string
     */
    public function getSecondPrizeTitleFR()
    {
        return $this->secondPrizeTitleFR;
    }

    /**
     * Set secondPrizeLogo
     *
     * @param string $secondPrizeLogo
     *
     * @return Challenge
     */
    public function setSecondPrizeLogo($secondPrizeLogo)
    {
        $this->secondPrizeLogo = $secondPrizeLogo;

        return $this;
    }

    /**
     * Get secondPrizeLogo
     *
     * @return string
     */
    public function getSecondPrizeLogo()
    {
        return $this->secondPrizeLogo;
    }

    /**
     * Set thirdPrizeTitleEN
     *
     * @param string $thirdPrizeTitleEN
     *
     * @return Challenge
     */
    public function setThirdPrizeTitleEN($thirdPrizeTitleEN)
    {
        $this->thirdPrizeTitleEN = $thirdPrizeTitleEN;

        return $this;
    }

    /**
     * Get thirdPrizeTitleEN
     *
     * @return string
     */
    public function getThirdPrizeTitleEN()
    {
        return $this->thirdPrizeTitleEN;
    }

    /**
     * Set thirdPrizeTitleFR
     *
     * @param string $thirdPrizeTitleFR
     *
     * @return Challenge
     */
    public function setThirdPrizeTitleFR($thirdPrizeTitleFR)
    {
        $this->thirdPrizeTitleFR = $thirdPrizeTitleFR;

        return $this;
    }

    /**
     * Get thirdPrizeTitleFR
     *
     * @return string
     */
    public function getThirdPrizeTitleFR()
    {
        return $this->thirdPrizeTitleFR;
    }

    /**
     * Set thirdPrizeLogo
     *
     * @param string $thirdPrizeLogo
     *
     * @return Challenge
     */
    public function setThirdPrizeLogo($thirdPrizeLogo)
    {
        $this->thirdPrizeLogo = $thirdPrizeLogo;

        return $this;
    }

    /**
     * Get thirdPrizeLogo
     *
     * @return string
     */
    public function getThirdPrizeLogo()
    {
        return $this->thirdPrizeLogo;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Challenge
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }
}
