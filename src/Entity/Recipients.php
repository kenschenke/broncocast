<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RecipientsRepository")
 *
 * @ORM\HasLifecycleCallbacks()
 */
class Recipients
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $broadcastId;

    /**
     * @ORM\Column(type="integer")
     */
    private $userId;

    /**
     * @ORM\Column(type="integer")
     */
    private $rev;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private $created;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private $updated;

    /**
     * @var Broadcasts
     * @ORM\ManyToOne(targetEntity="Broadcasts", inversedBy="recipients")
     * @ORM\JoinColumn(name="broadcast_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $broadcast;

    /**
     * @var Users
     * @ORM\ManyToOne(targetEntity="Users", inversedBy="broadcasts")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * Called when new record saved
     *
     * @ORM\PrePersist
     */
    public function onSaveNewRecord()
    {
        $this->created = $this->updated = new \DateTime();
        $this->rev = 1;
    }

    /**
     * Called when record updated
     *
     * @ORM\PreUpdate
     */
    public function onUpdateRecord()
    {
        $this->updated = new \DateTime();
        $this->rev++;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getBroadcastId(): ?int
    {
        return $this->broadcastId;
    }

    public function setBroadcastId(int $broadcastId): self
    {
        $this->broadcastId = $broadcastId;

        return $this;
    }

    /**
     * @return Broadcasts
     */
    public function getBroadcast()
    {
        return $this->broadcast;
    }

    /**
     * @param Broadcasts|null $broadcast
     * @return $this
     */
    public function setBroadcast(Broadcasts $broadcast = null)
    {
        $this->broadcast = $broadcast;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return Users
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param Users|null $user
     * @return $this
     */
    public function setUser(Users $user = null)
    {
        $this->user = $user;

        return $this;
    }

    public function getRev(): ?int
    {
        return $this->rev;
    }

    public function setRev(int $rev): self
    {
        $this->rev = $rev;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }
}
