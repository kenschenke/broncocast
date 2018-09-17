<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsersRepository")
 *
 * @ORM\HasLifecycleCallbacks()
 */
class Users implements \Serializable, UserInterface, EquatableInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $legacyPassword;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $salt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $fullname;

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->password,
            $this->legacyPassword,
            $this->salt,
            $this->isActive,
            $this->fullname,
            $this->singleMsg,
            $this->rev,
            $this->created,
            $this->updated,
        ]);
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->password,
            $this->legacyPassword,
            $this->salt,
            $this->isActive,
            $this->fullname,
            $this->singleMsg,
            $this->rev,
            $this->created,
            $this->updated,
            ) = unserialize($serialized);
    }

    /**
     * @ORM\Column(type="boolean")
     */
    private $singleMsg;

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
     * @ORM\OneToMany(targetEntity="Contacts", mappedBy="user")
     */
    private $contacts;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="GrpMembers", mappedBy="user")
     */
    private $groups;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Recipients", mappedBy="user")
     */
    private $broadcasts;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="OrgMembers", mappedBy="user")
     */
    private $orgs;

    /**
     * @ORM\Column(type="string", length=64, nullable=true, unique=true)
     */
    private $resetStr;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $resetExpire;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->broadcasts = new ArrayCollection();
        $this->orgs = new ArrayCollection();
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

    public function eraseCredentials()
    {
    }

    public function isEqualTo(UserInterface $user)
    {
        if ($this->id !== $user->getId()) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        return true;
    }

    public function getRoles()
    {
        $roles = ['ROLE_USER'];
        $orgs = $this->getOrgs();  // this is actually an array of OrgMember records
        $isAdmin = false;
        $isSystemAdmin = false;
        foreach ($orgs as $org) {
            if ($org->getIsAdmin()) {
                $isAdmin = true;
                if ($org->getOrgId() === 1) {
                    $isSystemAdmin = true;
                }
            }
        }
        if ($isAdmin) {
            $roles[] = 'ROLE_ADMIN';
            $roles[] = 'ROLE_ORG_ADMIN';
        }
        if ($isSystemAdmin) {
            $roles[] = 'ROLE_SYSTEM_ADMIN';
        }

        return $roles;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        $contacts = $this->getContacts();
        if ($contacts !== null && !$contacts->isEmpty()) {
            return $contacts->first()->getContact();
        } else {
            return 'n/a';
        }
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getLegacyPassword(): ?string
    {
        return $this->legacyPassword;
    }

    public function setLegacyPassword(string $legacyPassword): self
    {
        $this->legacyPassword = $legacyPassword;

        return $this;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function setSalt(string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): self
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getSingleMsg(): ?bool
    {
        return $this->singleMsg;
    }

    public function setSingleMsg(bool $singleMsg): self
    {
        $this->singleMsg = $singleMsg;

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
     * @param Contacts $contact
     * @return $this
     */
    public function addContact(Contacts $contact)
    {
        $this->contacts[] = $contact;

        return $this;
    }

    /**
     * @param Contacts $contact
     */
    public function removeContact(Contacts $contact)
    {
        $this->contacts->removeElement($contact);
    }

    /**
     * @return ArrayCollection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param GrpMembers $grpMember
     * @return $this
     */
    public function addGroup(GrpMembers $grpMember)
    {
        $this->groups[] = $grpMember;

        return $this;
    }

    /**
     * @param GrpMembers $grpMember
     */
    public function removeGroup(GrpMembers $grpMember)
    {
        $this->groups->removeElement($grpMember);
    }

    /**
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param Recipients $recipient
     * @return $this
     */
    public function addBroadcast(Recipients $recipient)
    {
        $this->broadcasts[] = $recipient;

        return $this;
    }

    /**
     * @param Recipients $recipient
     */
    public function removeBroadcast(Recipients $recipient)
    {
        $this->broadcasts->removeElement($recipient);
    }

    /**
     * @return ArrayCollection
     */
    public function getBroadcasts()
    {
        return $this->broadcasts;
    }

    /**
     * @param OrgMembers $orgMember
     * @return $this
     */
    public function addOrg(OrgMembers $orgMember)
    {
        $this->orgs[] = $orgMember;

        return $this;
    }

    /**
     * @param OrgMembers $orgMember
     */
    public function removeOrg(OrgMembers $orgMember)
    {
        $this->orgs->removeElement($orgMember);
    }

    /**
     * @return ArrayCollection
     */
    public function getOrgs()
    {
        return $this->orgs;
    }

    public function getResetStr(): ?string
    {
        return $this->resetStr;
    }

    public function setResetStr(?string $resetStr): self
    {
        $this->resetStr = $resetStr;

        return $this;
    }

    public function getResetExpire(): ?\DateTimeInterface
    {
        return $this->resetExpire;
    }

    public function setResetExpire(?\DateTimeInterface $resetExpire): self
    {
        $this->resetExpire = $resetExpire;

        return $this;
    }
}
