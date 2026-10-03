<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Users;

#[ORM\Entity]
class Follow
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'followerId', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Users $followerId;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'followingId', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Users $followingId;

    #[ORM\Column(type: "datetime", name: "followedAt", nullable: true)]
    private ?\DateTimeInterface $followedAt = null;

    #[ORM\Column(type: "string")]
    private string $status;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getFollowerId()
    {
        return $this->followerId;
    }

    public function setFollowerId($value)
    {
        $this->followerId = $value;
    }

    public function getFollowingId()
    {
        return $this->followingId;
    }

    public function setFollowingId($value)
    {
        $this->followingId = $value;
    }

    public function getFollowedAt()
    {
        return $this->followedAt;
    }

    public function setFollowedAt($value)
    {
        $this->followedAt = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }
}