<?php

namespace App\Command;

use Kreait\Firebase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendMessageCommand extends Command
{
    protected static $defaultName = 'app:fcm:send-message';
    /**
     * @var Firebase
     */
    private $firebase;

    public function __construct(Firebase $firebase)
    {
        $this->firebase = $firebase;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription("Send an FCM message");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $messaging = $this->firebase->getMessaging();

        $io = new SymfonyStyle($input, $output);

        $target = $io->choice('Please select a target', ['Topic', 'Condition', 'Registration Token']);
        $validator = static function ($answer) use ($target) {
            if (empty($target)) {
                throw new InvalidArgumentException('The ' . $target . ' must not be empty');
            }

            return $answer;
        };

        switch ($target) {
            case 'Topic':
                $topic = $io->ask('Please enter the name of the topic', null, $validator);
                $message = Firebase\Messaging\CloudMessage::withTarget(Firebase\Messaging\MessageTarget::TOPIC, $topic);
                break;

            case 'Condition':
                $condition = $io->ask('Please enter the condition', null, $validator);
                $message = Firebase\Messaging\CloudMessage::withTarget(Firebase\Messaging\MessageTarget::CONDITION, $condition);
                break;

            case 'Registration Token':
                $registrationToken = $io->ask('Please enter the registration token', null, $validator);
//                $message = Firebase\Messaging\CloudMessage::withTarget(Firebase\Messaging\MessageTarget::TOKEN, $registrationToken);
                $message = Firebase\Messaging\CloudMessage::withTarget(Firebase\Messaging\MessageTarget::TOKEN, $registrationToken);
                break;

            default:
                throw new InvalidArgumentException("Invalid message target {$target}");
        }

        $message = $message->withNotification([
            'title' => $io->ask('Please enter the title of the message'),
            'body' => $io->ask('Please enter the body of your message'),
        ]);

        $responseData = $messaging->send($message);

        $io->success('The message has been sent and the API returned the following:');
        $io->writeln(json_encode($responseData, JSON_PRETTY_PRINT));
    }
}