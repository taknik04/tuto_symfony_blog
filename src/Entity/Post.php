<?php

namespace App\Entity;

use App\Entity\Category;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;



#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;



    #[ORM\ManyToOne(inversedBy: 'posts')]
    private ?User $user = null;


    #[Assert\NotBlank(message: "La catégorie est obligatoire.")]
    #[Assert\Type(
        type: Category::class,
        message: "Veuillez choisir une catégorie valide.",
    )]
    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;


    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le titre ne doit pas dépasser {{ limit }} caractères.",
    )]
    #[ORM\Column(length: 255)]
    private ?string $title = null;


    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(length: 255)]
    private ?string $slug = null;


    #[ORM\Column]
    private ?bool $isPublished = null;


    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Assert\File(
        maxSize: '2048k',
        extensions: ['png', 'jpg', 'jpeg', 'webp'],
        maxSizeMessage: 'Veuillez insérer une image dont la taille est inférieure à 2Mo',
        extensionsMessage: "Seuls les formats 'png', 'jpg', 'jpeg', 'webp' sont acceptés",
    )]
    #[Vich\UploadableField(mapping: 'images_posts', fileNameProperty: 'image')]
    private ?File $imageFile = null;


    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $image = null;



    #[Assert\NotBlank(message: "Le contenu est obligatoire.")]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;


    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;


    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;



    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'posts')]
    private Collection $tags;

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Comment::class, orphanRemoval: true)]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: PostLike::class, orphanRemoval: true)]
    private Collection $postLikes;

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: PostLike::class, orphanRemoval: true)]
    private Collection $yes;


    public function __construct()
    {
        $this->isPublished = false;
        $this->tags = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->postLikes = new ArrayCollection();
        $this->yes = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function isIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    public function isLikedBy(User $user) : bool
    {
        // Récupérons tous les likes associés à cet article
        $likes = $this->getPostLikes()->toArray();

        // Parcourons le tableau des likes,
        foreach ($likes as $like) 
        {
            // Si l'utilisateur associé à l'un des likes est le même que l'utilisateur connecté
            if ( $like->getUser() == $user ) 
            {
                // c'est qu'il a déjà aimé cet article
                return true;
            }
        }
        
        // Dans le cas contraire, c'est qu'il n'a pas encore aimé cet article
        return false;
    }

    /**
     * @return Collection<int, PostLike>
     */
    public function getPostLikes(): Collection
    {
        return $this->postLikes;
    }

    public function addPostLike(PostLike $postLike): static
    {
        if (!$this->postLikes->contains($postLike)) {
            $this->postLikes->add($postLike);
            $postLike->setPost($this);
        }

        return $this;
    }

    public function removePostLike(PostLike $postLike): static
    {
        if ($this->postLikes->removeElement($postLike)) {
            // set the owning side to null (unless already changed)
            if ($postLike->getPost() === $this) {
                $postLike->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PostLike>
     */
    public function getYes(): Collection
    {
        return $this->yes;
    }

    public function addYe(PostLike $ye): static
    {
        if (!$this->yes->contains($ye)) {
            $this->yes->add($ye);
            $ye->setPost($this);
        }

        return $this;
    }

    public function removeYe(PostLike $ye): static
    {
        if ($this->yes->removeElement($ye)) {
            // set the owning side to null (unless already changed)
            if ($ye->getPost() === $this) {
                $ye->setPost(null);
            }
        }


        return $this;
    }

}
