<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrgsRepository")
 *
 * @ORM\HasLifecycleCallbacks()
 */
class Orgs
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $orgName;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $defaultTz;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $tag;

    /**
     * @ORM\Column(type="smallint")
     */
    private $maxBrcAge;

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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Broadcasts", mappedBy="org")
     */
    private $broadcasts;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="UserGrps", mappedBy="org")
     */
    private $groups;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="OrgMembers", mappedBy="org")
     */
    private $users;

    public function __construct()
    {
        $this->broadcasts = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

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

    public function getOrgName(): ?string
    {
        return $this->orgName;
    }

    public function setOrgName(string $orgName): self
    {
        $this->orgName = $orgName;

        return $this;
    }

    public function getDefaultTz(): ?string
    {
        return $this->defaultTz;
    }

    public function setDefaultTz(string $defaultTz): self
    {
        $this->defaultTz = $defaultTz;

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getMaxBrcAge(): ?int
    {
        return $this->maxBrcAge;
    }

    public function setMaxBrcAge(int $maxBrcAge): self
    {
        $this->maxBrcAge = $maxBrcAge;

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

    /**
     * @param Broadcasts $broadcast
     * @return $this
     */
    public function addBroadcast(Broadcasts $broadcast)
    {
        $this->broadcasts[] = $broadcast;

        return $this;
    }

    /**
     * @param Broadcasts $broadcast
     */
    public function removeBroadcast(Broadcasts $broadcast)
    {
        $this->broadcasts->removeElement($broadcast);
    }

    /**
     * @param UserGrps $group
     * @return $this
     */
    public function addGroup(UserGrps $group)
    {
        $this->groups[] = $group;

        return $this;
    }

    /**
     * @param UserGrps $group
     */
    public function removeGroup(UserGrps $group)
    {
        $this->groups->removeElement($group);
    }

    /**
     * @param OrgMembers $orgMember
     * @return $this
     */
    public function addMember(OrgMembers $orgMember)
    {
        $this->users[] = $orgMember;

        return $this;
    }

    /**
     * @param OrgMembers $orgMember
     */
    public function removeMember(OrgMembers $orgMember)
    {
        $this->users->removeElement($orgMember);
    }

    /**
     * @return ArrayCollection
     */
    public function getMembers()
    {
        return $this->users;
    }
}
