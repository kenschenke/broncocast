<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrgMembersRepository")
 *
 * @ORM\HasLifecycleCallbacks()
 */
class OrgMembers
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
    private $orgId;

    /**
     * @ORM\Column(type="integer")
     */
    private $userId;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAdmin;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isApproved;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $altUsrName;

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
     * @var Orgs
     * @ORM\ManyToOne(targetEntity="Orgs", inversedBy="users")
     * @ORM\JoinColumn(name="org_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $org;

    /**
     * @var Users
     * @ORM\ManyToOne(targetEntity="Users", inversedBy="orgs")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isHidden;

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

    public function getOrgId(): ?int
    {
        return $this->orgId;
    }

    public function setOrgId(int $orgId): self
    {
        $this->orgId = $orgId;

        return $this;
    }

    /**
     * @return Orgs
     */
    public function getOrg()
    {
        return $this->org;
    }

    /**
     * @param Orgs|null $org
     * @return $this
     */
    public function setOrg(Orgs $org = null)
    {
        $this->org = $org;

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

    public function getIsAdmin(): ?bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): self
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    public function getIsApproved(): ?bool
    {
        return $this->isApproved;
    }

    public function setIsApproved(bool $isApproved): self
    {
        $this->isApproved = $isApproved;

        return $this;
    }

    public function getAltUsrName(): ?string
    {
        return $this->altUsrName;
    }

    public function setAltUsrName(?string $altUsrName): self
    {
        $this->altUsrName = $altUsrName;

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

    public function getIsHidden(): ?bool
    {
        return $this->isHidden;
    }

    public function setIsHidden(bool $isHidden): self
    {
        $this->isHidden = $isHidden;

        return $this;
    }
}
