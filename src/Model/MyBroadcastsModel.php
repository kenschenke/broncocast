<?php

namespace App\Model;

use App\Util\AdminChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MyBroadcastsModel
{
    private $em;
    private $tokenStorage;
    private $adminChecker;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage,
                                AdminChecker $adminChecker)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->adminChecker = $adminChecker;
    }

    public function GetBroadcasts()
    {
        try {
            $User = $this->tokenStorage->getToken()->getUser();

            $Broadcasts = [];
            foreach ($User->getBroadcasts() as $Recip) {
                $Broadcast = $Recip->getBroadcast();
                $Delivered = $Broadcast->getDelivered();
                if (is_null($Delivered)) {
                    continue;
                }

                $LongMsg = $Broadcast->getLongMsg();
                if (is_null($LongMsg)) {
                    $LongMsg = '';
                }

                $AttachmentUrl = '';
                foreach ($Broadcast->getAttachments() as $attachment) {
                    $AttachmentUrl = '/api/broadcasts/attachments/' . $attachment->getId();
                    break;
                }

                $Broadcasts[] = [
                    'BroadcastId' => $Broadcast->getId(),
                    'ShortMsg' => $Broadcast->getShortMsg(),
                    'LongMsg' => $LongMsg,
                    'Delivered' => $Delivered->format('D M j, Y g:i a'),
                    'Timestamp' => $Delivered->getTimestamp(),
                    'UsrName' => $Broadcast->getUsrName(),
                    'AttachmentUrl' => $AttachmentUrl,
                ];
            }

            return ['Success' => true, 'Broadcasts' => $Broadcasts];
        }
        catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }

    public function ViewAttachment($Id)
    {
        try {
            $Attachment = $this->em->getRepository('App:Attachments')->find($Id);
            if (is_null($Attachment)) {
                throw new \Exception('Attachment record not found');
            }

            // If the user is not an admin of this organization, make sure they
            // were a recipient of this broadcast

            $Broadcast = $Attachment->getBroadcast();
            if (!$this->adminChecker->IsAdminUser($Broadcast->getOrgId())) {
                // Go through the recipients looking for this user
                $UserId = $this->tokenStorage->getToken()->getUser()->getId();
                $IsUserRecipient = false;
                foreach ($Broadcast->getRecipients() as $Recipient) {
                    if ($Recipient->getUserId() === $UserId) {
                        $IsUserRecipient = true;
                        break;
                    }
                }
                if (!$IsUserRecipient) {
                    throw new \Exception('You are not authorized to view this attachment');
                }
            }

            $filename = getenv('BRONCOCAST_ATTACHMENTS_DIR') . '/' . $Attachment->getLocalName();
            if (!file_exists($filename)) {
                throw new \Exception('Attachment file not found');
            }
            $Response = new BinaryFileResponse($filename);
            $Response->headers->set('Content-Type', $Attachment->getMimeType());
            return $Response;
        } catch (\Exception $e) {
            $content =
                "<!DOCTYPE html>\n" .
                "<html><head>" .
                '<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">' .
                '<meta http-equiv="X-UA-Compatible" content="IE=edge">' .
                '<meta name="viewport" content="width=device-width, initial-scale=1">' .
                '<title>BroncoCast</title>' .
                '<link rel="stylesheet" type="text/css" href="/bronco-cast.css">' .
                '</head><body>' .
                '<div class="alert alert-danger" style="margin:15px">' .
                '<h3>An Error Occurred</h3>' .
                "<p>" . htmlentities($e->getMessage()) . "</p></div>" .
                '</body></html>';
            return new Response($content);
        }
    }
}
