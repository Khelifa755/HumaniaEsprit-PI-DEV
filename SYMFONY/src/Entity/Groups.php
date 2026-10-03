<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Users;
use Doctrine\Common\Collections\Collection;
use App\Entity\Publication;

#[ORM\Entity]
#[ORM\Table(name: '`groups`')]
class Groups
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: "string", length: 100, name: 'name')]
    private string $name;

    #[ORM\Column(type: "text", name: 'description')]
    private string $description;

        #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "groupss")]
    #[ORM\JoinColumn(name: 'createdById', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Users $createdById;

    #[ORM\Column(type: "datetime", name: 'createdAt')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: "string", length: 500, name: 'imageUrl')]
    private string $imageUrl;

    #[ORM\Column(type: "integer", name: 'memberCount')]
    private int $memberCount;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($value)
    {
        $this->name = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getCreatedById()
    {
        return $this->createdById;
    }

    public function setCreatedById($value)
    {
        $this->createdById = $value;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($value)
    {
        $this->createdAt = $value;
    }

    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    public function setImageUrl($value)
    {
        $this->imageUrl = $value;
    }

    public function getMemberCount()
    {
        return $this->memberCount;
    }

    public function setMemberCount($value)
    {
        $this->memberCount = $value;
    }

    #[ORM\OneToMany(mappedBy: "groupId", targetEntity: Groupmember::class)]
    private Collection $groupmembers;

        public function getGroupmembers(): Collection
        {
            return $this->groupmembers;
        }
    
        public function addGroupmember(Groupmember $groupmember): self
        {
            if (!$this->groupmembers->contains($groupmember)) {
                $this->groupmembers[] = $groupmember;
                $groupmember->setGroupId($this);
            }
    
            return $this;
        }
    
        public function removeGroupmember(Groupmember $groupmember): self
        {
            if ($this->groupmembers->removeElement($groupmember)) {
                // set the owning side to null (unless already changed)
                if ($groupmember->getGroupId() === $this) {
                    $groupmember->setGroupId(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "groupId", targetEntity: Publication::class)]
    private Collection $publications;
}
