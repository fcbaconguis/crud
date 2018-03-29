<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContractRepository")
 * @Vich\Uploadable
 */
class Contract
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="contracts", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="set null")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name_final;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_updated;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Page", mappedBy="contract", cascade={"remove"}, orphanRemoval=true))
     */
    private $pages;

    /**
     * @Vich\UploadableField(mapping="contract_name", fileNameProperty="name")
     * 
     * @var File
     */
    private $file;


    public function __construct()
    {
        $this->pages = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNameFinal(): ?string
    {
        return $this->name_final;
    }

    public function setNameFinal(string $name_final): self
    {
        $this->name_final = $name_final;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface
    {
        return $this->date_updated;
    }

    public function setDateUpdated(\DateTimeInterface $date_updated): self
    {
        $this->date_updated = $date_updated;

        return $this;
    }

    /**
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $contract
     */
    public function setFile(?File $contract = null): void
    {
        $this->file = $contract;

        if (null !== $contract) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->date_updated = new \DateTimeImmutable();
        }
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return Collection|Page[]
     */
    public function getPages()
    {
        return $this->pages;
    }
}
