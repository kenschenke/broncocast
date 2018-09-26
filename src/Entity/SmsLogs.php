<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SmsLogsRepository")
 *
 * @ORM\HasLifecycleCallbacks()
 */
class SmsLogs
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
    private $contactId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $message;

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
     * @var Contacts
     * @ORM\ManyToOne(targetEntity="Contacts", inversedBy="smsLogs")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $contact;

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

    public function getContactId(): ?int
    {
        return $this->contactId;
    }

    public function setContactId(int $contactId): self
    {
        $this->contactId = $contactId;

        return $this;
    }

    /**
     * @return Contacts
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param Contacts|null $contact
     * @return $this
     */
    public function setContact(Contacts $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

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
