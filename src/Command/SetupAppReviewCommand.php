<?php

namespace App\Command;

use App\Entity\Broadcasts;
use App\Entity\Contacts;
use App\Entity\Groups;
use App\Entity\GrpMembers;
use App\Entity\OrgMembers;
use App\Entity\Orgs;
use App\Entity\Recipients;
use App\Entity\Users;
use App\Security\PwdHelper;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class SetupAppReviewCommand extends Command
{
    const AppReviewEmail = 'app.reviewer@example.com';
    const AppReviewName = 'App Reviewer';
    const AppReviewOrg = 'Super Robotics Team';
    const AppReviewPassword = 'App$Revi3w';
    const AppReviewTag = 'APPREVIEW';

    /** @var EntityManagerInterface */
    private $em;

    /** @var PwdHelper */
    private $pwdHelper;

    /** @var Orgs */
    private $appReviewOrg;

    /** @var string */
    private $timezone;

    /** @var Groups */
    private $students;

    /** @var Groups */
    private $parents;

    /** @var Groups */
    private $mentors;

    private $firstNames = [
        'Bob', 'Jason', 'Molly', 'Susan', 'Brian', 'Aidan', 'Chris', 'Kelly', 'John', 'Jennifer',
        'Melissa', 'Michael', 'Steve', 'David', 'Frank', 'Greg', 'Hannah', 'Keith', 'Nancy', 'Oscar',
        'Patty', 'Rachael', 'Tracy', 'Wayne', 'Mason', 'Jacob', 'Ethan', 'James', 'Daniel', 'Liam',
        'Olivia', 'Emily', 'Madison', 'Emma', 'Sophia', 'Ava', 'Mia'
    ];

    private $lastNames = [
        'Edwards', 'Smith', 'Jones', 'Clancy', 'Brown', 'Davis', 'Wilson', 'Williams', 'Johnson',
        'Taylor', 'Lopez', 'Young', 'Miller', 'Harris', 'Moore', 'Patterson', 'Jenkins', 'Long',
        'Foster', 'Gray', 'Hall', 'Stewart', 'Ross', 'Bryant', 'Russell', 'Bell', 'Coleman', 'White',
        'Martin', 'Anderson'
    ];

    private $mentorUserIds = [];
    private $studentUserIds = [];
    private $parentUserIds = [];

    public function __construct(EntityManagerInterface $em, PwdHelper $pwdHelper, ?string $name = null)
    {
        $this->em = $em;
        $this->pwdHelper = $pwdHelper;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('bcast:appreview')
            ->setDescription('Set up some test data for app store review');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $output->writeln("App Review Setup\n");

        $output->writeln(
            "This command prepares the BroncoCast database for app review. " .
            "It creates a separate organization, some bogus users, and some bogus " .
            "content for app review in the Apple App Store. This action is non-destructive " .
            "but does create some database records. If the app review organization already " .
            "exists, its contents are erased and reset. This command is safe to run on a " .
            "production BroncoCast installation. Other organizations, reviews, and " .
            "broadcasts are untouched.\n"
        );

        $confirm = new ConfirmationQuestion("\nContinue with setup? (y/n) ", false);
        if (!$helper->ask($input, $output, $confirm)) {
            return;
        }

        $conn = $this->em->getConnection();

        $this->askForTimezone($helper, $input, $output);
        $this->createOrg();
        $this->purgeRecords($conn);
        $this->createGroups();
        $this->createAppReviewUser();
        $this->createUsers();
        $this->createBroadcasts();

        $output->writeln('The app review data has been created.');
    }

    private function addUserToGroup(Users $user, Groups $group)
    {
        $grpMember = new GrpMembers();
        $grpMember->setUser($user);
        $grpMember->setGroup($group);
        $this->em->persist($grpMember);
        $this->em->flush();
    }

    private function askForTimezone(QuestionHelper $helper, InputInterface $input, OutputInterface $output)
    {
        $timezones = \DateTimeZone::listIdentifiers();
        // show link to list of timezones and use autocomplete

        $output->writeln("Setup needs to know your time zone.  That is, the time zone " .
            "in which the broadcasts will be sent and received.  This is not necessarily the time zone " .
            "of the web server, especially if it's operated by a hosting provider.  A list of acceptable " .
            "time zones is located at http://php.net/manual/en/timezones.php\n");

        $question = new Question("Please enter your time zone: [$this->timezone] ", $this->timezone);
        while (true) {
            $timezone = $helper->ask($input, $output, $question);
            if (in_array($timezone, $timezones)) {
                $this->timezone = $timezone;
                break;
            }
        }
    }

    private function createAppReviewUser()
    {
        // Create the user record

        $user = new Users();
        $user->setFullname(self::AppReviewName);
        $user->setIsActive(true);
        $user->setLegacyPassword('');
        $user->setSalt('');
        $user->setSingleMsg(false);
        $this->pwdHelper->SaveUserPassword($user, self::AppReviewPassword);
        $this->em->persist($user);
        $this->em->flush();

        // Add the user to the org

        $orgMember = new OrgMembers();
        $orgMember->setUser($user);
        $orgMember->setOrg($this->appReviewOrg);
        $orgMember->setIsHidden(false);
        $orgMember->setIsAdmin(true);
        $orgMember->setIsApproved(true);
        $this->em->persist($orgMember);
        $this->em->flush();

        // Add the user to each of the three groups

        $this->addUserToGroup($user, $this->mentors);
        $this->addUserToGroup($user, $this->students);
        $this->addUserToGroup($user, $this->parents);

        $this->mentorUserIds[] = $user->getId();
        $this->studentUserIds[] = $user->getId();
        $this->parentUserIds[] = $user->getId();

        // Add an email contact record

        $contactEmail = new Contacts();
        $contactEmail->setContact(self::AppReviewEmail);
        $contactEmail->setContactType(Contacts::TYPE_EMAIL);
        $contactEmail->setUser($user);
        $this->em->persist($contactEmail);
        $this->em->flush();

        // Add a phone contact record

        $contactPhone = new Contacts();
        $contactPhone->setContact($this->generateRandomPhone());
        $contactPhone->setContactType(Contacts::TYPE_PHONE);
        $contactPhone->setUser($user);
        $this->em->persist($contactPhone);
        $this->em->flush();
    }

    private function createBroadcast($message, $userIds)
    {
        // Generate a random delivery time

        $hoursAgo = rand(4, 72);
        $delivered = new \DateTime();
        $delivered->sub(new \DateInterval("PT${hoursAgo}H"));

        // Create the broadcast record

        $broadcast = new Broadcasts();
        $broadcast->setOrg($this->appReviewOrg);
        $broadcast->setShortMsg($message);
        $broadcast->setLongMsg('');
        $broadcast->setCancelled(false);
        $broadcast->setDelivered($delivered);
        $broadcast->setUsrName(self::AppReviewName);
        $this->em->persist($broadcast);
        $this->em->flush();

        // Add the recipients

        $repo = $this->em->getRepository('App:Users');
        foreach ($userIds as $userId) {
            /** @var Users $user */
            $user = $repo->find($userId);
            if (is_null($user)) {
                throw new \Exception('User record not found');
            }
            $recipient = new Recipients();
            $recipient->setBroadcast($broadcast);
            $recipient->setUser($user);
            $this->em->persist($recipient);
        }
        $this->em->flush();
    }

    private function createBroadcasts()
    {
        // Generate a broadcast for students
        $message =
            'Shop will open at 10 am.  If you will be driving yourself, ' .
            'your parent must call a coach first.  Be careful - the roads ' .
            'are very slick!';
        $this->createBroadcast($message, $this->studentUserIds);

        // Generate a broadcast for mentors
        $message =
            'Mentor meeting Saturday morning at 8:30.  We will be discussing ' .
            'robot progress and preparations for the upcoming competitions.';
        $this->createBroadcast($message, $this->mentorUserIds);

        // Generate a broadcast for all users
        $message =
            'Pre-competition meeting next Monday at 7 pm.  All team members ' .
            'and parents must attend.';
        $all = array_unique(array_merge($this->studentUserIds, $this->parentUserIds, $this->mentorUserIds));
        $this->createBroadcast($message, $all);
    }

    private function createGroups()
    {
        $this->students = new Groups();
        $this->students->setGrpName('Students');
        $this->students->setOrg($this->appReviewOrg);
        $this->em->persist($this->students);
        $this->em->flush();

        $this->parents = new Groups();
        $this->parents->setGrpName('Parents');
        $this->parents->setOrg($this->appReviewOrg);
        $this->em->persist($this->parents);
        $this->em->flush();

        $this->mentors = new Groups();
        $this->mentors->setGrpName('Mentors');
        $this->mentors->setOrg($this->appReviewOrg);
        $this->em->persist($this->mentors);
        $this->em->flush();
    }

    private function createOrg()
    {
        // Check to see if the org already exists

        $this->appReviewOrg = $this->em->getRepository('App:Orgs')->findOneBy(['tag' => self::AppReviewTag]);

        if (is_null($this->appReviewOrg)) {
            // Nope.  Create it.

            $this->appReviewOrg = new Orgs();
            $this->appReviewOrg->setOrgName(self::AppReviewOrg);
            $this->appReviewOrg->setDefaultTz($this->timezone);
            $this->appReviewOrg->setTag(self::AppReviewTag);
            $this->appReviewOrg->setMaxBrcAge(0);
            $this->em->persist($this->appReviewOrg);
            $this->em->flush();
        }
    }

    private function createUser(Groups $group, &$userIds)
    {
        // Generate a random name

        $name = $this->generateRandomName();

        // Make the name into an email address

        $email = str_replace(' ', '.', $name) . '@example.com';

        // Create the user record

        $user = new Users();
        $user->setFullname($name);
        $user->setIsActive(true);
        $user->setLegacyPassword('');
        $user->setSalt('');
        $user->setSingleMsg(false);
        $this->pwdHelper->SaveUserPassword($user, $this->generateRandomPassword());
        $this->em->persist($user);
        $this->em->flush();
        $userIds[] = $user->getId();

        // Add the user to the group

        $this->addUserToGroup($user, $group);

        // Add the user to the org

        $orgMember = new OrgMembers();
        $orgMember->setUser($user);
        $orgMember->setOrg($this->appReviewOrg);
        $orgMember->setIsHidden(false);
        $orgMember->setIsAdmin(false);
        $orgMember->setIsApproved(true);
        $this->em->persist($orgMember);
        $this->em->flush();

        // Add an email contact record

        $contactEmail = new Contacts();
        $contactEmail->setContact($email);
        $contactEmail->setContactType(Contacts::TYPE_EMAIL);
        $contactEmail->setUser($user);
        $this->em->persist($contactEmail);
        $this->em->flush();

        // Add a phone contact record

        $contactPhone = new Contacts();
        $contactPhone->setContact($this->generateRandomPhone());
        $contactPhone->setContactType(Contacts::TYPE_PHONE);
        $contactPhone->setUser($user);
        $this->em->persist($contactPhone);
        $this->em->flush();
    }

    private function createUsers()
    {
        // Generate five students

        for ($i = 1; $i <= 5; $i++) {
            $this->createUser($this->students, $this->studentUserIds);
        }

        // Generate five parents

        for ($i = 1; $i <= 5; $i++) {
            $this->createUser($this->parents, $this->parentUserIds);
        }

        // Generate five mentors

        for ($i = 1; $i <= 5; $i++) {
            $this->createUser($this->mentors, $this->mentorUserIds);
        }
    }

    private function generateRandomName()
    {
        $first = $this->firstNames[array_rand($this->firstNames)];
        $last = $this->lastNames[array_rand($this->lastNames)];
        return "$first $last";
    }

    private function generateRandomPassword()
    {
        $password = '';

        for ($i = 1; $i <= 25; $i++)
            $password .= chr(rand(65, 65+25));

        return $password;
    }

    private function generateRandomPhone()
    {
        $phone = (string)(rand(2, 9));
        for ($i = 1; $i <= 9; $i++) {
            $phone .= (string)(rand(0, 9));
        }

        return $phone;
    }

    private function purgeRecords(Connection $conn)
    {
        // Delete broadcasts

        $conn->executeQuery('DELETE FROM recipients WHERE broadcast_id IN ' .
            '(SELECT id FROM broadcasts WHERE org_id = :OrgId)', ['OrgId' => $this->appReviewOrg->getId()]);
        $conn->executeQuery('DELETE FROM attachments WHERE broadcast_id IN ' .
            '(SELECT id FROM broadcasts WHERE org_id = :OrgId)', ['OrgId' => $this->appReviewOrg->getId()]);
        $conn->executeQuery('DELETE FROM broadcasts WHERE org_id = :OrgId',
            ['OrgId' => $this->appReviewOrg->getId()]);

        // Delete groups

        $conn->executeQuery('DELETE FROM grp_members WHERE grp_id IN ' .
            '(SELECT id FROM groups WHERE org_id = :OrgId)', ['OrgId' => $this->appReviewOrg->getId()]);
        $conn->executeQuery('DELETE FROM groups WHERE org_id = :OrgId',
            ['OrgId' => $this->appReviewOrg->getId()]);

        // Delete users, but only those that are only in the app review org

        $stmt = $conn->executeQuery('SELECT user_id FROM org_members WHERE org_id = :OrgId',
            ['OrgId' => $this->appReviewOrg->getId()]);
        $userRepo = $this->em->getRepository('App:Users');
        while (($row = $stmt->fetch(\PDO::FETCH_NUM))) {
            /** @var Users $user */
            $user = $userRepo->find($row[0]);
            if (is_null($user)) {
                throw new \Exception('User record not found');
            }

            // If the user is not a member of any other org - delete it
            if ($user->getOrgs()->count() == 1) {
                $conn->executeQuery('DELETE FROM org_members WHERE user_id = :UserId',
                    ['UserId' => $user->getId()]);
                $conn->executeQuery('DELETE FROM contacts WHERE user_id = :UserId',
                    ['UserId' => $user->getId()]);
                $conn->executeQuery('DELETE FROM users WHERE id = :UserId',
                    ['UserId' => $user->getId()]);
            }
        }
    }
}
