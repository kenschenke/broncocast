<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BroadcastsRepository")
 *
 * @ORM\HasLifecycleCallbacks()
 */
class Broadcasts
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
    private $usrName;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $scheduled;

    /**
     * @ORM\Column(type="string", length=140)
     */
    private $shortMsg;

    /**
     * @ORM\Column(type="string", length=2048, nullable=true)
     */
    private $longMsg;

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
     * @ORM\ManyToOne(targetEntity="Orgs", inversedBy="broadcasts")
     * @ORM\JoinColumn(name="org_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $org;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Attachments", mappedBy="broadcast")
     */
    private $attachments;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Recipients", mappedBy="broadcast")
     */
    private $recipients;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isSent;

    public function __construct()
    {
        $this->attachments = new ArrayCollection();
        $this->recipients = new ArrayCollection();
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

    public function setOrgId(int $orgId): self
    {
        $this->orgId = $orgId;

        return $this;
    }

    public function getUsrName(): ?string
    {
        return $this->usrName;
    }

    public function setUsrName(string $usrName): self
    {
        $this->usrName = $usrName;

        return $this;
    }

    public function getScheduled(): ?\DateTimeInterface
    {
        return $this->scheduled;
    }

    public function setScheduled(?\DateTimeInterface $scheduled): self
    {
        $this->scheduled = $scheduled;

        return $this;
    }

    public function getShortMsg(): ?string
    {
        return $this->shortMsg;
    }

    public function setShortMsg(string $shortMsg): self
    {
        $this->shortMsg = $shortMsg;

        return $this;
    }

    public function getLongMsg(): ?string
    {
        return $this->longMsg;
    }

    public function setLongMsg(string $longMsg): self
    {
        $this->longMsg = $longMsg;

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
     * @param Attachments $attachment
     * @return $this
     */
    public function addAttachment(Attachments $attachment)
    {
        $this->attachments[] = $attachment;

        return $this;
    }

    /**
     * @param Attachments $attachment
     */
    public function removeAttachment(Attachments $attachment)
    {
        $this->attachments->removeElement($attachment);
    }

    /**
     * @return ArrayCollection
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param Recipients $recipient
     * @return $this
     */
    public function addRecipient(Recipients $recipient)
    {
        $this->recipients[] = $recipient;

        return $this;
    }

    /**
     * @param Recipients $recipient
     */
    public function removeRecipient(Recipients $recipient)
    {
        $this->recipients->removeElement($recipient);
    }

    /**
     * @return ArrayCollection
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    public function getIsSent(): ?bool
    {
        return $this->isSent;
    }

    public function setIsSent(bool $isSent): self
    {
        $this->isSent = $isSent;

        return $this;
    }
}
