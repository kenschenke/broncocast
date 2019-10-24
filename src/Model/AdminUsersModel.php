<?php

namespace App\Model;

use App\Entity\Contacts;
use App\Util\AdminChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdminUsersModel
{
    const SmsErrors = [
        21211,  // This phone number is invalid
        21612,  // Cannot route to this number
        21408,  // Cannot route to international number
        21610,  // This number is blacklisted
        21615,  // This number cannot receive SMS messages
    ];

    private $tokenStorage;
    private $adminChecker;
    private $em;
    private $requestStack;

    public function __construct(TokenStorageInterface $tokenStorage, AdminChecker $adminChecker,
                                EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->tokenStorage = $tokenStorage;
        $this->adminChecker = $adminChecker;
        $this->em = $entityManager;
        $this->requestStack = $requestStack;
    }

    public function ApproveUser($MemberId)
    {
        try {
            $OrgMember = $this->em->getRepository('App:OrgMembers')->find($MemberId);
            if (is_null($OrgMember)) {
                throw new \Exception('Member record not found');
            }

            if (!$this->adminChecker->IsAdminUser($OrgMember->getOrgId())) {
                throw new \Exception('Administrative privileges required');
            }

            $OrgMember->setIsApproved(true);
            $this->em->persist($OrgMember);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function ChangeName($MemberId)
    {
        try {
            $request = $this->requestStack->getCurrentRequest();
            if (!$request->request->has('Name')) {
                throw new \Exception('Name parameter missing');
            }
            $Name = trim($request->request->get('Name'));

            $OrgMember = $this->em->getRepository('App:OrgMembers')->find($MemberId);
            if (is_null($OrgMember)) {
                throw new \Exception('Member record not found');
            }

            if (!$this->adminChecker->IsAdminUser($OrgMember->getOrgId())) {
                throw new \Exception('Administrative privileges required');
            }

            $OrgMember->setAltUsrName($Name);
            $this->em->persist($OrgMember);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function GetUsers($OrgId)
    {
        try {
            if (!$this->adminChecker->IsAdminUser($OrgId)) {
                throw new \Exception('Administrative privileges required');
            }

            $userRepo = $this->em->getRepository('App:Users');
            $groupRepo = $this->em->getRepository('App:Groups');

            $org = $this->em->getRepository('App:Orgs')->find($OrgId);
            if (is_null($org)) {
                throw new \Exception('Organization record not found');
            }

            $Users = [];
            $stmt = $this->em->getConnection()->executeQuery(
                'SELECT org_members.id AS member_id, is_admin, is_approved, is_hidden, ' .
                'user_id, alt_usr_name, fullname, single_msg FROM org_members ' .
                'JOIN users ON org_members.user_id = users.id ' .
                'WHERE org_id = :OrgId', ['OrgId' => $OrgId]
            );

            while (($row = $stmt->fetch(\PDO::FETCH_ASSOC))) {
                $MemberId = (int)$row['member_id'];
                $UserId = (int)$row['user_id'];
                $AltUsrName = $row['alt_usr_name'];
                $UserName = $row['fullname'];
                $IsAdmin = $row['is_admin'] ? true : false;
                $IsApproved = $row['is_approved'] ? true : false;
                $Hidden = $row['is_hidden'] ? true : false;
                $SingleMsg = $row['single_msg'] ? true : false;
                if (!is_null($AltUsrName) && !empty($AltUsrName))
                    $UserName = $AltUsrName;

                $Users[$UserId] = [
                    'MemberId' => $MemberId,
                    'UsrName' => $UserName,
                    'IsAdmin' => $IsAdmin,
                    'Approved' => $IsApproved,
                    'Hidden' => $Hidden,
                    'SingleMsg' => $SingleMsg,
                    'MobileApp' => '',
                    'Contacts' => [],
                    'Groups' => [],
                    'SmsLogs' => [],
                    'HasDeliveryError' => false,
                ];
            }

            $SmsLogs = [];
            $stmt = $this->em->getConnection()->executeQuery(
                'SELECT contact_id, code, message, created FROM sms_logs ORDER BY created DESC'
            );
            while (($row = $stmt->fetch(\PDO::FETCH_ASSOC))) {
                $ContactId = $row['contact_id'];
                $Code = $row['code'];
                $Message = $row['message'];
                $Time = new \DateTime($row['created']);

                if (!array_key_exists($ContactId, $SmsLogs)) {
                    $SmsLogs[$ContactId] = [];
                }
                $SmsLogs[$ContactId][] = [
                    'Code' => $Code,
                    'Message' => $Message,
                    'Time' => $Time->format('m/d/Y h:i:s a')
                ];
            }

            $stmt = $this->em->getConnection()->executeQuery(
                'SELECT id, user_id, contact, contact_type FROM contacts'
            );
            while (($row = $stmt->fetch(\PDO::FETCH_NUM))) {
                $ContactId = $row[0];
                $UserId = $row[1];
                $Contact = $row[2];
                $Type = $row[3];

                $MobileApp = '';
                if ($Type === Contacts::TYPE_APPLE || $Type === Contacts::TYPE_FCM_APPLE) {
                    $MobileApp = 'Apple';
                } elseif ($Type === Contacts::TYPE_FCM_ANDROID) {
                    $MobileApp = 'Android';
                }

                if (array_key_exists($UserId, $Users)) {
                    if (!empty($MobileApp)) {
                        if (empty($Users[$UserId]['MobileApp'])) {
                            $Users[$UserId]['MobileApp'] = $MobileApp;
                        }

                        // Don't show mobile device tokens in the user list
                        continue;
                    }

                    $Users[$UserId]['Contacts'][] = [
                        'ContactId' => $ContactId,
                        'Contact' => $Contact
                    ];

                    $HasDeliveryError = false;
                    if (array_key_exists($ContactId, $SmsLogs)) {
                        foreach ($SmsLogs[$ContactId] as $smsLog) {
                            $Users[$UserId]['SmsLogs'][] = [
                                'ContactId' => $ContactId,
                                'Code' => $smsLog['Code'],
                                'Message' => $smsLog['Message'],
                                'Time' => $smsLog['Time'],
                            ];
                            if (in_array((int)$smsLog['Code'], self::SmsErrors)) {
                                $HasDeliveryError = true;
                            }
                        }
                    }
                    $Users[$UserId]['HasDeliveryError'] = $HasDeliveryError;
                }
            }

            $stmt = $this->em->getConnection()->executeQuery(
                'SELECT user_id, grp_name FROM grp_members ' .
                'JOIN groups ON grp_members.grp_id = groups.id ' .
                'WHERE org_id = :OrgId', ['OrgId' => $OrgId]
            );
            while (($row = $stmt->fetch(\PDO::FETCH_NUM))) {
                $UserId = $row[0];
                $GrpName = $row[1];

                if (array_key_exists($UserId, $Users)) {
                    $Users[$UserId]['Groups'][] = $GrpName;
                }
            }

            $UserResults = [];
            foreach ($Users as $UserId => $User) {
                $UserResults[] = [
                    'MemberId' => $User['MemberId'],
                    'UsrId' => $UserId,
                    'UsrName' => $User['UsrName'],
                    'IsAdmin' => $User['IsAdmin'],
                    'Approved' => $User['Approved'],
                    'Hidden' => $User['Hidden'],
                    'SingleMsg' => $User['SingleMsg'],
                    'MobileApp' => $User['MobileApp'],
                    'Groups' => implode(', ', $User['Groups']),
                    'Contacts' => $User['Contacts'],
                    'SmsLogs' => $User['SmsLogs'],
                    'HasDeliveryError' => $User['HasDeliveryError'],
                ];
            }

            return ['Success' => true, 'Users' => $UserResults];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function RemoveUser($MemberId)
    {
        try {
            $OrgMember = $this->em->getRepository('App:OrgMembers')->find($MemberId);
            if (is_null($OrgMember)) {
                throw new \Exception('Member record not found');
            }

            if (!$this->adminChecker->IsAdminUser($OrgMember->getOrgId())) {
                throw new \Exception('Administrative privileges required');
            }

            // Drop group membership records

            $this->em->getConnection()->executeQuery(
                "DELETE FROM grp_members WHERE user_id = ? AND " .
                "grp_id IN (SELECT id FROM groups WHERE org_id = ?)",
                [$OrgMember->getUserId(), $OrgMember->getOrgId()]);

            $this->em->remove($OrgMember);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function SetUserAdmin($MemberId, $IsAdmin)
    {
        try {
            $OrgMember = $this->em->getRepository('App:OrgMembers')->find($MemberId);
            if (is_null($OrgMember)) {
                throw new \Exception('Member record not found');
            }

            if (!$this->adminChecker->IsAdminUser($OrgMember->getOrgId())) {
                throw new \Exception('Administrative privileges required');
            }

            $OrgMember->setIsAdmin($IsAdmin);
            $this->em->persist($OrgMember);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function SetUserHidden($MemberId, $Hidden)
    {
        try {
            $OrgMember = $this->em->getRepository('App:OrgMembers')->find($MemberId);
            if (is_null($OrgMember)) {
                throw new \Exception('Member record not found');
            }

            if (!$this->adminChecker->IsAdminUser($OrgMember->getOrgId())) {
                throw new \Exception('Administrative privileges required');
            }

            $OrgMember->setIsHidden($Hidden);
            $this->em->persist($OrgMember);
            $this->em->flush();

            return ['Success' => true];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }
}
