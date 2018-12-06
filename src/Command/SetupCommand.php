<?php

namespace App\Command;

use App\Entity\Contacts;
use App\Entity\OrgMembers;
use App\Entity\Orgs;
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

class SetupCommand extends Command
{
    private $em;
    private $pwdHelper;
    private $timezone;
    private $systemOrg;
    private $defaultOrg;

    public function __construct(EntityManagerInterface $em, PwdHelper $pwdHelper, ?string $name = null)
    {
        parent::__construct($name);

        $this->em = $em;
        $this->pwdHelper = $pwdHelper;

        $this->timezone = date_default_timezone_get();
        $this->systemOrg = New Orgs();
        $this->defaultOrg = new Orgs();
    }

    protected function configure()
    {
        $this
            ->setName('bcast:setup')
            ->setDescription('Setup Broncocast for the first time');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $output->writeln("Welconme to BroncoCast!\n");
        $output->writeln(
            "This command performs the initial database configuration " .
            "for BroncoCast.  Continue only if this is a new install of BroncoCast " .
            "and the database has been initialized by running all available Doctrine " .
            "migrations.  See the README for more information on the installation process.\n");
        $output->writeln("C A U T I O N ! !\n");
        $output->writeln("DO NOT PERFORM THIS STEP ON AN " .
            "INSTALLATION THAT IS ALREADY UP AND RUNNING.\n\nIt makes changes to your " .
            "database that are destructive to an installation that is already set up.");

        $confirm = new ConfirmationQuestion("\nContinue with setup? (y/n) ", false);
        if (!$helper->ask($input, $output, $confirm)) {
            return;
        }
        $output->writeln("");

        $conn = $this->em->getConnection();

        if (!$this->doesTheOrgsTableExist($conn)) {
            $output->writeln("It does not look like the database is set up.  Aborting.");
            return;
        }

        if (!$this->isTheDatabaseEmpty($conn)) {
            $output->writeln("It looks like the database already contains data.  Aborting.");
            return;
        }

        $output->writeln("Please answer the following questions to set up the BroncoCast " .
            "database tables.  If a default is available, it will be shown in [square brackets].\n");

        $this->askForTimezone($helper, $input, $output);
        $this->createTheSystemOrg($input, $output);
        $this->createTheDefaultOrg($helper, $input, $output);

        $this->createTheSystemAdminUser($helper, $input, $output);

        $output->writeln("\nSetup is complete.");
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

    private function createTheDefaultOrg(QuestionHelper $helper, InputInterface $input, OutputInterface $output)
    {
        $output->writeln("\nSetup will now create an organization for the users.  BroncoCast supports " .
            "hosting more than one organization on a single installation.  You can create more organizations " .
            "later, but for now enter your organization name (up to 30 characters).\n");

        $nameQuestion = new Question("Enter organization name: ");
        while(true) {
            $orgName = $helper->ask($input, $output, $nameQuestion);
            if (strlen($orgName) <= 30) {
                $this->defaultOrg->setOrgName($orgName);
                break;
            }
        }

        $output->writeln("\nThe new organization needs a short tag.  This is a short (up to 15 characters) " .
            "word or phrase with only letters or numbers (no spaces or punctuation).  When users register on " .
            "BroncoCast, they might need to type in this tag to join the organization.\n");

        $tagQuestion = new Question("Enter organization tag: ");
        while(true) {
            $tagName = strtoupper(trim($helper->ask($input, $output, $tagQuestion)));
            if (preg_match('/[^0-9A-Z]/', $tagName) !== 0) {
                $output->writeln("\nThe tag can contain only letters and numbers\n");
                continue;
            }
            if (strlen($tagName) <= 15) {
                $this->defaultOrg->setTag($tagName);
                break;
            }
        }

        $this->defaultOrg->setDefaultTz($this->timezone);
        $this->defaultOrg->setMaxBrcAge(0);
        $this->em->persist($this->defaultOrg);
        $this->em->flush();
    }

    private function createTheSystemAdminUser(QuestionHelper $helper, InputInterface $input, OutputInterface $output)
    {
        $output->writeln("\nNow it's time to create a user account for you.  This account will be a system " .
            "administrator with admin privileges in all organizations on this installation of BroncoCast.  " .
            "Additional system administrators can be added by making them members of the System Organization.  " .
            "Please note that system administrators have the ability to create and delete organizations.  Only " .
            "users that need to be able to add or delete organizations should be made system admins.\n");

        $User = new Users();
        $User->setLegacyPassword('');
        $User->setSalt('');
        $User->setIsActive(true);
        $User->setSingleMsg(false);

        $nameQuestion = new Question("First and last name (up to 30 characters): ");
        while (true) {
            $name = $helper->ask($input, $output, $nameQuestion);
            if (strlen($name) <= 30) {
                $User->setFullname($name);
                break;
            }
        }

        $pwdQuestion = new Question("Please enter your password: ");
        $pwdQuestion->setHidden(true);
        $pwdQuestion->setHiddenFallback(false);
        $pwdConfirmQuestion = new Question("Please confirm your password: ");
        $pwdConfirmQuestion->setHidden(true);
        $pwdConfirmQuestion->setHiddenFallback(false);
        while (true) {
            $password1 = $helper->ask($input, $output, $pwdQuestion);
            $password2 = $helper->ask($input, $output, $pwdConfirmQuestion);
            if ($password1 === $password2) {
                $this->pwdHelper->SaveUserPassword($User, $password1);
                break;
            }

            $output->writeln("Passwords do not match.\n");
        }

        $emailQuestion = new Question("Please enter your email address: ");
        $email = $helper->ask($input, $output, $emailQuestion);

        $this->em->persist($User);
        $this->em->flush();

        $systemOrgMember = new OrgMembers();
        $systemOrgMember->setIsAdmin(true);
        $systemOrgMember->setIsApproved(true);
        $systemOrgMember->setIsHidden(false);
        $systemOrgMember->setOrg($this->systemOrg);
        $systemOrgMember->setUser($User);
        $this->em->persist($systemOrgMember);

        $orgMember = new OrgMembers();
        $orgMember->setIsAdmin(true);
        $orgMember->setIsApproved(true);
        $orgMember->setIsHidden(false);
        $orgMember->setOrg($this->defaultOrg);
        $orgMember->setUser($User);
        $this->em->persist($orgMember);

        $contact = new Contacts();
        $contact->setContact($email);
        $contact->setUser($User);
        $this->em->persist($contact);

        $this->em->flush();
    }

    private function createTheSystemOrg(InputInterface $input, OutputInterface $output)
    {
        $this->systemOrg->setOrgName('System Organization');
        $this->systemOrg->setDefaultTz($this->timezone);
        $this->systemOrg->setTag('');
        $this->systemOrg->setMaxBrcAge(0);
        $this->em->persist($this->systemOrg);
        $this->em->flush();

        if ($this->systemOrg->getId() !== 1) {
            $output->writeln("The System Organization should have had an Id of 1.  It's possible " .
                "this database was not completely cleaned.");
        }
    }

    private function doesTheOrgsTableExist(Connection $conn)
    {
        $stmt = $conn->executeQuery('SHOW TABLES');
        $found = false;
        while (($row = $stmt->fetch(\PDO::FETCH_NUM))) {
            if ($row[0] === 'orgs') {
                $found = true;
                break;
            }
        }

        return $found;
    }

    private function isTheDatabaseEmpty(Connection $conn)
    {
        return $this->isTheTableEmpty($conn,'orgs') &&
            $this->isTheTableEmpty($conn, 'users') &&
            $this->isTheTableEmpty($conn, 'contacts') &&
            $this->isTheTableEmpty($conn, 'org_members');
    }

    private function isTheTableEmpty(Connection $conn, $table)
    {
        $stmt = $conn->executeQuery("SELECT COUNT(*) FROM $table");
        if (!($row = $stmt->fetch(\PDO::FETCH_NUM))) {
            return false;
        }

        return (int)$row[0] === 0;
    }
}
