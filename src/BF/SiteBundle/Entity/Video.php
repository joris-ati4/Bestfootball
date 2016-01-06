<?php

namespace BF\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BF\UserBundle\Entity;
use BF\SiteBundle\Entity\Challenge;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\TimeCode;

//declaration de la variable extension
$extension = null;


/**
 * Video
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="BF\SiteBundle\Entity\VideoRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Video
{

    /**
    * @ORM\ManyToOne(targetEntity="BF\UserBundle\Entity\User", inversedBy="videos")
    * @ORM\JoinColumn(nullable=false)
    */
    private $user;

    /**
    * @ORM\ManyToOne(targetEntity="BF\SiteBundle\Entity\Challenge", inversedBy="videos")
    * @ORM\JoinColumn(nullable=true)
    */
    private $challenge;

    /**
    * @ORM\ManyToOne(targetEntity="BF\SiteBundle\Entity\Duel", inversedBy="videos")
    * @ORM\JoinColumn(nullable=true)
    */
    private $duel;

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
     * @ORM\Column(name="source", type="string", length=255)
     */
    private $source;

    /**
     * @var string
     *
     * @ORM\Column(name="thumb_url", type="string", length=255)
     */
    private $thumbUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="thumb_alt", type="string", length=255)
     */
    private $thumbAlt;

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
    * @ORM\Column(name="date", type="datetime")
    */
    private $date;

    /**
    * @ORM\Column(name="repetitions", type="integer", length=10)
    */
    private $repetitions;

    /**
    * @ORM\Column(name="score", type="integer", length=10, nullable=true)
    */
    private $score;

    /**
    * @ORM\Column(name="type", type="string", length=20, nullable=false)
    */
    private $type;

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
      if (null !== $this->source) {
        // On sauvegarde l'extension du fichier pour le supprimer plus tard
        $this->tempFilename = $this->source;
        // On réinitialise les valeurs des attributs url et alt
        $this->source = null;
        $this->thumbUrl = null;
        $this->thumbAlt = null;
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
      $this->source = 'mp4';
      global $extension;
      $extension = $this->file->guessExtension();
      // Et on génère l'attribut alt de la balise <img>, à la valeur du nom du fichier sur le PC de l'internaute
      $this->thumbAlt = $this->file->getClientOriginalName();
      $this->thumbUrl = '/var/www/bestfootball.fr/shared/web/uploads/img'.$this->id.'.jpg';
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
      global $extension;
      $this->file->move(
        $this->getUploadRootDir(), // Le répertoire de destination
        $this->id.'.'.$extension  // Le nom du fichier à créer, ici « id.extension »
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
        $video = $ffmpeg->open($this->getUploadRootDir().'/'.$this->id.'.'.$extension);

        $video
            ->frame( TimeCode::fromSeconds(1))
            ->save('/var/www/bestfootball.fr/shared/web/uploads/videos/thumbnail/'.$this->id.'.jpg');
        // Resize to 1280x720 to compact the video ! 
        $video
            ->filters()
            ->resize(new Dimension(1280, 720), ResizeFilter::RESIZEMODE_INSET)
            ->synchronize();

        // Start transcoding and save video
            if($extension != 'mp4')
            {
                 $video->save(new X264(),'/var/www/bestfootball.fr/shared/web/uploads/videos/'.$this->id.'.mp4');
                 unlink($this->getUploadRootDir().'/'.$this->id.'.'.$extension);
            }

       
    }

    /**
     * @ORM\PreRemove()
     */
    public function preRemoveUpload()
    {
      // On sauvegarde temporairement le nom du fichier, car il dépend de l'id
      $this->tempFilename = $this->getUploadRootDir().'/'.$this->id.'.'.$this->source;
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
      return 'uploads/videos';
    }

    protected function getUploadRootDir()
    {
      // On retourne le chemin relatif vers l'image pour notre code PHP
      return '/var/www/bestfootball.fr/shared/web/'.$this->getUploadDir();
    }




    public function __construct()
    {
        $this->date = new \Datetime();
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
     * Set source
     *
     * @param string $source
     *
     * @return Video
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set thumbUrl
     *
     * @param string $thumbUrl
     *
     * @return Video
     */
    public function setThumbUrl($thumbUrl)
    {
        $this->thumbUrl = $thumbUrl;

        return $this;
    }

    /**
     * Get thumbUrl
     *
     * @return string
     */
    public function getThumbUrl()
    {
        return $this->thumbUrl;
    }

    /**
     * Set thumbAlt
     *
     * @param string $thumbAlt
     *
     * @return Video
     */
    public function setThumbAlt($thumbAlt)
    {
        $this->thumbAlt = $thumbAlt;

        return $this;
    }

    /**
     * Get thumbAlt
     *
     * @return string
     */
    public function getThumbAlt()
    {
        return $this->thumbAlt;
    }

    /**
     * Set user
     *
     * @param \BF\UserBundle\Entity\User $user
     *
     * @return Video
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
     * Set challenge
     *
     * @param \BF\SiteBundle\Entity\Challenge $challenge
     *
     * @return Video
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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Video
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
     * Set title
     *
     * @param string $title
     *
     * @return Video
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
     * @return Video
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
     * Set repetitions
     *
     * @param integer $repetitions
     *
     * @return Video
     */
    public function setRepetitions($repetitions)
    {
        $this->repetitions = $repetitions;

        return $this;
    }

    /**
     * Get repetitions
     *
     * @return integer
     */
    public function getRepetitions()
    {
        return $this->repetitions;
    }

    /**
     * Set score
     *
     * @param integer $score
     *
     * @return Video
     */
    public function setScore($score)
    {
        $this->score = $score;

        return $this;
    }

    /**
     * Get score
     *
     * @return integer
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Video
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
     * Set duel
     *
     * @param \BF\SiteBundle\Entity\Duel $duel
     *
     * @return Video
     */
    public function setDuel(\BF\SiteBundle\Entity\Duel $duel = null)
    {
        $this->duel = $duel;

        return $this;
    }

    /**
     * Get duel
     *
     * @return \BF\SiteBundle\Entity\Duel
     */
    public function getDuel()
    {
        return $this->duel;
    }
}
