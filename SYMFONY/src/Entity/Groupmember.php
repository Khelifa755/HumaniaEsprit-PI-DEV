<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Users;

#[ORM\Entity]
class Groupmember
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Groups::class, inversedBy: "groupmembers")]
    #[ORM\JoinColumn(name: 'groupId', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Groups $groupId;

        #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "groupmembers")]
    #[ORM\JoinColumn(name: 'userId', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Users $userId;

    #[ORM\Column(type: "string", length: 20, name: 'role')]
    private string $role;

    #[ORM\Column(type: "datetime", name: 'joinedAt')]
    private \DateTimeInterface $joinedAt;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getGroupId()
    {
        return $this->groupId;
    }

    public function setGroupId($value)
    {
        $this->groupId = $value;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($value)
    {
        $this->userId = $value;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($value)
    {
        $this->role = $value;
    }

    public function getJoinedAt()
    {
        return $this->joinedAt;
    }

    public function setJoinedAt($value)
    {
        $this->joinedAt = $value;
    }
}
