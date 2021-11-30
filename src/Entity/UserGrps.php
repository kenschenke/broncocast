<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupsRepository")
 *
 * @ORM\HasLifecycleCallbacks()
 */
class UserGrps
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
     * @ORM\Column(type="string", length=30)
     */
    private $grpName;

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
     * @ORM\ManyToOne(targetEntity="Orgs", inversedBy="groups")
     * @ORM\JoinColumn(name="org_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $org;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="GrpMembers", mappedBy="group")
     */
    private $members;

    public function __construct()
    {
        $this->members = new ArrayCollection();
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

    public function getGrpName(): ?string
    {
        return $this->grpName;
    }

    public function setGrpName(string $grpName): self
    {
        $this->grpName = $grpName;

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
     * @param GrpMembers $grpMember
     * @return $this
     */
    public function addMember(GrpMembers $grpMember)
    {
        $this->members[] = $grpMember;

        return $this;
    }

    /**
     * @param GrpMembers $grpMember
     */
    public function removeMember(GrpMembers $grpMember)
    {
        $this->members->removeElement($grpMember);
    }
}
