<?php

namespace App\Model;

use App\Entity\Attachments;
use App\Entity\Broadcasts;
use App\Entity\UserGrps;
use App\Entity\GrpMembers;
use App\Entity\Orgs;
use App\Entity\Recipients;
use App\Entity\Users;
use App\Util\AdminChecker;
use App\Util\UploadFile;
use App\Util\UserUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdminBroadcastsModel
{
    private $em;
    private $adminChecker;
    private $uploadFile;
    private $requestStack;
    private $tokenStorage;
    private $userUtil;

    public function __construct(EntityManagerInterface $em, AdminChecker $adminChecker,
                                UploadFile $uploadFile, RequestStack $requestStack,
                                TokenStorageInterface $tokenStorage, UserUtil $userUtil)
    {
        $this->em = $em;
        $this->adminChecker = $adminChecker;
        $this->uploadFile = $uploadFile;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
        $this->userUtil = $userUtil;

        $this->uploadFile->addPermittedType('application/pdf');
        $this->uploadFile->addPermittedType('application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->uploadFile->addPermittedType('application/rtf');
        $this->uploadFile->addPermittedType('image/jpeg');
        $this->uploadFile->addPermittedType('image/pjpeg');
        $this->uploadFile->addPermittedType('image/gif');
        $this->uploadFile->addPermittedType('image/png');
        $this->uploadFile->addPermittedType('image/webp');
    }

    public function CancelBroadcast($BroadcastId)
    {
        try {
            $Broadcast = $this->em->getRepository('App:Broadcasts')->find($BroadcastId);
            if (is_null($Broadcast)) {
                throw new \Exception('Broadcast record not found');
            }

            if (!$this->adminChecker->IsAdminUser($Broadcast->getOrgId())) {
                throw new \Exception('Administrative privileges required');
            }

            if (!is_null($Broadcast->getDelivered())) {
                throw new \Exception('Broadcast already delivered');
            }

            $Broadcast->setCancelled(true);
            $this->em->persist($Broadcast);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function GetBroadcasts($OrgId)
    {
        try {
            if (!$this->adminChecker->IsAdminUser($OrgId)) {
                throw new \Exception('Administrative privileges required');
            }
            $Org = $this->em->getRepository('App:Orgs')->find($OrgId);
            if (is_null($Org)) {
                throw new \Exception('Organization record not found');
            }

            $Broadcasts = [];
            $recs = $this->em->getRepository('App:Broadcasts')->findBy(['orgId' => $OrgId]);
            /** @var Broadcasts $Broadcast */
            foreach ($recs as $Broadcast) {
                $LongMsg = $Broadcast->getLongMsg();
                if (is_null($LongMsg)) {
                    $LongMsg = '';
                }

                // The time for the broadcast is the delivered time (if delivered).  Otherwise it's the
                // scheduled time.
                $Scheduled = $Broadcast->getScheduled();
                if (!is_null($Scheduled)) {
                    // The Scheduled date is stored in UTC in the database but the database doesn't
                    // remember the timezone so it assumes server's local timezone.  Convert it back
                    // UTC for display purposes.
                    $Scheduled->setTimestamp($Scheduled->getTimestamp() + $Scheduled->getOffset());
                }
                $Created = $Broadcast->getCreated();
                $Delivered = $Broadcast->getDelivered();
                if (is_null($Delivered)) {
                    $Time = is_null($Scheduled) ? $Created : $Scheduled;
                } else {
                    $Time = $Delivered;
                }
                $Timestamp = $Time->getTimestamp();

                $AttachmentUrl = '';
                foreach ($Broadcast->getAttachments() as $attachment) {
                    $AttachmentUrl = '/api/broadcasts/attachments/' . $attachment->getId();
                    break;
                }

                $Recipients = [];
                $OrgMemberRepo = $this->em->getRepository('App:OrgMembers');
                /** @var Recipients $recipient */
                foreach ($Broadcast->getRecipients() as $recipient) {
                    $User = $recipient->getUser();
                    $UserName = $User->getFullname();
                    $OrgMember = $OrgMemberRepo->findOneBy(['orgId' => $OrgId, 'userId' => $User->getId()]);
                    if (!is_null($OrgMember)) {
                        $AltUserName = $OrgMember->getAltUsrName();
                        if (!is_null($AltUserName) && !empty(trim($AltUserName))) {
                            $UserName = $AltUserName;
                        }
                    }
                    $Recipients[] = $UserName;
                }
                sort($Recipients);

                $Broadcasts[] = [
                    'BroadcastId' => $Broadcast->getId(),
                    'ShortMsg' => $Broadcast->getShortMsg(),
                    'LongMsg' => $LongMsg,
                    'Time' => $Time->format('D M j, Y g:i a'),
                    'Timestamp' => $Timestamp,
                    'IsDelivered' => !is_null($Delivered),
                    'IsCancelled' => $Broadcast->getCancelled(),
                    'UsrName' => $Broadcast->getUsrName(),
                    'AttachmentUrl' => $AttachmentUrl,
                    'Recipients' => $Recipients,
                ];
            }

            return ['Success' => true, 'Broadcasts' => $Broadcasts];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function GetGroupMemberships($OrgId)
    {
        try {
            if (!$this->adminChecker->IsAdminUser($OrgId)) {
                throw new \Exception('Administrative privileges required');
            }

            $GrpMembersRepo = $this->em->getRepository('App:GrpMembers');
            $UsersRepo = $this->em->getRepository('App:Users');
            $OrgMembersRepo = $this->em->getRepository('App:OrgMembers');

            $Groups = [];
            $GroupRecs = $this->em->getRepository('App:UserGrps')->findBy(['orgId' => $OrgId]);
            /** @var UserGrps $Group */
            foreach ($GroupRecs as $Group) {
                $Members = [];
                $MemberRecs = $GrpMembersRepo->findBy(['grpId' => $Group->getId()]);
                /** @var GrpMembers $GrpMember */
                foreach ($MemberRecs as $GrpMember) {
                    $User = $UsersRepo->find($GrpMember->getUserId());
                    if (is_null($User)) {
                        throw new \Exception('User record not found');
                    }
                    $OrgMember = $OrgMembersRepo->findOneBy(['orgId' => $OrgId, 'userId' => $User->getId()]);
                    if (is_null($OrgMember)) {
                        $UserId = $User->getId();
                        throw new \Exception("Organization member record not found: OrgId:${OrgId}, UserId:${UserId}");
                    }
                    if ($OrgMember->getIsHidden()) {
                        continue;  // don't bother if they're hidden
                    }

                    $UserName = $User->getFullname();
                    $AltUsrName = $OrgMember->getAltUsrName();
                    if (!is_null($AltUsrName) && !empty($AltUsrName)) {
                        $UserName = $AltUsrName;
                    }

                    $Members[] = [
                        'UserId' => $User->getId(),
                        'UserName' => $UserName,
                    ];
                }

                usort($Members, function ($m1, $m2) {
                    if ($m1['UserName'] > $m2['UserName']) {
                        return 1;
                    } elseif ($m1['UserName'] < $m2['UserName']) {
                        return -1;
                    } else {
                        return 0;
                    }
                });

                $Groups[$Group->getGrpName()] = $Members;
            }

            return ['Success' => true, 'Groups' => $Groups];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function HandleUpload()
    {
        try {
            $found = false;
            $localName = '';
            $mimeType = '';

            $request = $this->requestStack->getCurrentRequest();
            foreach ($request->files as $file) {
                if (is_null($file)) {
                    continue;
                }

                if ($found) {
                    throw new \Exception('Only one attachment can be uploaded');
                }

                $mimeType = $file->getMimeType();
                if (!$this->uploadFile->upload($file, getenv('BRONCOCAST_ATTACHMENTS_DIR'))) {
                    throw new \Exception(implode(' ', $this->uploadFile->getMessages()));
                }

                $localName = $this->uploadFile->getLocalName();
                if (is_null($localName)) {
                    throw new \Exception('Local attachment name not available');
                }

                $found = true;
            }

            if (!$found) {
                throw new \Exception('No attachment was uploaded');
            }

            return [
                'Success' => true,
                'LocalName' => $localName,
                'MimeType' => $mimeType,
            ];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function SaveNewBroadcast($OrgId)
    {
        try {
            if (!$this->adminChecker->IsAdminUser($OrgId)) {
                throw new \Exception('Administrative privileges required');
            }
            /** @var Orgs $Org */
            $Org = $this->em->getRepository('App:Orgs')->find($OrgId);
            if (is_null($Org)) {
                throw new \Exception('Organization record not found');
            }

            $request = $this->requestStack->getCurrentRequest();
            if (!$request->request->has('ShortMsg')) {
                throw new \Exception('ShortMsg parameter missing');
            }
            if (!$request->request->has('Recipients')) {
                throw new \Exception('Recipients parameter missing');
            }

            $ShortMsg = $request->request->get('ShortMsg');
            $LongMsg = $request->request->get('LongMsg', '');
            $RecipientsParam = $request->request->get('Recipients');
            $ScheduledParam = $request->request->get('Scheduled', '');
            $TimeZone = $request->request->get('TimeZone', '');
            $AttachmentFriendlyName = $request->request->get('AttachmentFriendlyName', '');
            $AttachmentLocalName = $request->request->get('AttachmentLocalName', '');
            $AttachmentMimeType = $request->request->get('AttachmentMimeType', '');

            if (strlen($ShortMsg) > 140) {
                throw new \Exception('ShortMsg parameter too long');
            }
            if (strlen($LongMsg) > 2048) {
                throw new \Exception('LongMsg parameter too long');
            }

            // If the short message and long message are identical, ignore the long message.
            if ($ShortMsg === $LongMsg) {
                $LongMsg = '';
            }

            if (!empty($ScheduledParam)) {
                if (empty($TimeZone)) {
                    throw new \Exception('Missing TimeZone parameter');
                }
                $timezones = \DateTimeZone::listIdentifiers();
                if (!in_array($TimeZone, $timezones)) {
                    throw new \Exception('Invalid TimeZone parameter');
                }

                $Scheduled = new \DateTime($ScheduledParam, new \DateTimeZone($TimeZone));
                $Scheduled->setTimezone(new \DateTimeZone('UTC'));
                $Time = new \DateTime();
                $Time->setTimestamp($Scheduled->getTimestamp() + $Scheduled->getOffset());
            } else {
                $Scheduled = null;
                $Time = new \DateTime();
            }

            $User = $this->tokenStorage->getToken()->getUser();
            $Broadcast = new Broadcasts();
            $Broadcast->setOrg($Org);
            $Broadcast->setUsrName($User->getFullname());
            $Broadcast->setShortMsg($ShortMsg);
            $Broadcast->setCancelled(false);
            if (!empty($LongMsg)) {
                $Broadcast->setLongMsg($LongMsg);
            }
            if (!is_null($Scheduled)) {
                $Broadcast->setScheduled($Scheduled);
            }
            $this->em->persist($Broadcast);
            $this->em->flush();

            $Recipients = explode(',', $RecipientsParam);
            $RecipientNames = [];
            $UsersRepo = $this->em->getRepository('App:Users');
            foreach ($Recipients as $UserId) {
                /** @var Users $User */
                $User = $UsersRepo->find($UserId);
                if (is_null($User)) {
                    throw new \Exception('User record not found');
                }
                $Recipient = new Recipients();
                $Recipient->setBroadcast($Broadcast);
                $Recipient->setUser($User);
                $this->em->persist($Recipient);

                $RecipientNames[] = $this->userUtil->GetUserName($UserId, $OrgId);
            }

            if (!empty($AttachmentLocalName) && !empty($AttachmentMimeType)) {
                $Attachment = new Attachments();
                $Attachment->setBroadcast($Broadcast);
                $Attachment->setFriendlyName($AttachmentFriendlyName);
                $Attachment->setLocalName($AttachmentLocalName);
                $Attachment->setMimeType($AttachmentMimeType);
                $this->em->persist($Attachment);
            }

            $this->em->flush();

            return [
                'Success' => true,
                'BroadcastId' => $Broadcast->getId(),
                'ShortMsg' => $ShortMsg,
                'LongMsg' => $LongMsg,
                'Time' => $Time->format('D M j, Y g:i a'),
                'Timestamp' => $Time->getTimestamp(),
                'IsDelivered' => false,
                'UsrName' => $Broadcast->getUsrName(),
                'Recipients' => $RecipientNames,
            ];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }
}
