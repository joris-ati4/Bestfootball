<?php

namespace BF\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Format\Video\X264;
use FFMpeg\Format\Video\WebM;
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
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

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
     * @ORM\Column(name="gold", type="string", length=255)
     */
    private $gold;

    /**
     * @var string
     *
     * @ORM\Column(name="silver", type="string", length=255)
     */
    private $silver;

    /**
     * @var string
     *
     * @ORM\Column(name="bronze", type="string", length=255)
     */
    private $bronze;

    /**
    * @ORM\Column(name="date", type="datetime")
    */
    private $date;

     private $file;

    // On ajoute cet attribut pour y stocker le nom du fichier temporairement
    private $tempFilename;


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
      $this->videoUrl = 'webm';
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
        $this->id.'.'.$this->videoUrl  // Le nom du fichier à créer, ici « id.extension »
      );

      //here we will convert the video to mp4 and webm and save the thumbnail.

      //the file is stocked in /Foot/web/uploads/videos/ + filename

      //this is the code to convert the file we receive into webm and mp4. We need to change to accept all file sizes.
        $ffmpeg = FFMpeg::create(array(
            'ffmpeg.binaries'  => 'ffmpeg\bin\ffmpeg',
            'ffprobe.binaries' => 'ffmpeg\bin\ffprobe',
            'timeout'          => 3600, // The timeout for the underlying process
            'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use
        ));

        // Open video
        $video = $ffmpeg->open($this->getUploadRootDir().'/'.$this->id.'.'.$this->videoUrl);

        $video
            ->frame( TimeCode::fromSeconds(1))
            ->save('C:\wamp\www\Foot\web\uploads\challenges\thumbnail/'.$this->id.'.jpg');
        // Resize to 1280x720 to compact the video ! 
        $video
            ->filters()
            ->resize(new Dimension(1280, 720), ResizeFilter::RESIZEMODE_INSET)
            ->synchronize();

        // Start transcoding and save video
            if($this->videoUrl != 'webm')
            {
                 $video->save(new webm(),'C:\wamp\www\Foot\web\uploads\challenges/'.$this->id.'.webm');
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
      return __DIR__.'/../../../../web/'.$this->getUploadDir();
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
     * Set description
     *
     * @param string $description
     *
     * @return Challenge
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
     * Set gold
     *
     * @param string $gold
     *
     * @return Challenge
     */
    public function setGold($gold)
    {
        $this->gold = $gold;

        return $this;
    }

    /**
     * Get gold
     *
     * @return string
     */
    public function getGold()
    {
        return $this->gold;
    }

    /**
     * Set silver
     *
     * @param string $silver
     *
     * @return Challenge
     */
    public function setSilver($silver)
    {
        $this->silver = $silver;

        return $this;
    }

    /**
     * Get silver
     *
     * @return string
     */
    public function getSilver()
    {
        return $this->silver;
    }

    /**
     * Set bronze
     *
     * @param string $bronze
     *
     * @return Challenge
     */
    public function setBronze($bronze)
    {
        $this->bronze = $bronze;

        return $this;
    }

    /**
     * Get bronze
     *
     * @return string
     */
    public function getBronze()
    {
        return $this->bronze;
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
}
