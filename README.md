# BroncoCast

A message broadcast solution.

BroncoCast was developed to support a high school robotics team - 
FRC 1987, The Broncobots [http://www.teambroncobots.com](http://www.teambroncobots.com).
We needed a way to easily send out email and text message blasts to our team's
students, mentors, and parents.  BroncoCast is a web-based solution written
in PHP on the server (using the [Symfony](http://www.symfony.com) framework),
and React.js for the client.

Email messages are sent using [MailGun](http://www.mailgun.com), a low-cost
email delivery service.  If your volume is low, the setvice is free.  SMS
text messages are sent using [Twilio](http://www.twilio.com), a low-cost
SMS delivery service.

## Environment Requirements:
* [NodeJs](http://nodejs.org)
* [PHP](http://php.net)
* [MySql](http://mysql.org)
* [Composer](http://getcomposer.org)

## Install

1. `git clone https://github.com/kenschenke/broncocast.git`
2. `cd broncocast`
3. `npm install`
4. `composer install`
5. Create a MySQL database
6. Sign up for an account on [MailGun](http://www.mailgun.com).
7. Sign up for an account on [Twilio](http://www.twilio.com).
8. Configure Symfony environment variables (see below)
9. Copy the server and client files to your web server.
9. Set up the database tables
10. Configure your web server
11. Configure the periodic cron job on your web server (see below)

## Symfony Environment Variables

The server portion of BroncoCast requires several environment variables
to be set.  Symfony also requires several environment variables.
These need to be set in two places: the shell script that runs the periodic
cron job and the web server.  See your web server's documentation for more
information on setting environment variables.

The BroncoCast environment variables are:

| Environment Variable       | Description                                                        |
| -------------------------- | ------------------------------------------------------------------ |
| DEFAULT_ORG_TAG            | The tag for the organization new users are automatically added to. |
| BRONCOCAST_ATTACHMENTS_DIR | Full path of the attachments folder (see local folders)            |
| PERIODIC_LOCK_DIR          | Full path of the lock directory used by the periodic cron job.     | 
| ADMIN_EMAIL                | Email address of the system admin.                                 |
| MAILGUN_API_KEY            | The API key for MailGun.                                           |
| EMAIL_FROM_ADDR            | The from address for all outgoing emails.                          |
| TWILIO_SID                 | The SID for your Twilio account.                                   |
| TWILIO_AUTHTOKEN           | The AuthToken for your Twilio account.                             |
| TWILIO_FROM_NUMBER         | The sending phone number for your Twilio account.                  |

## Database Set Up

BroncoCast uses [Doctrine](http://www.doctrine-project.org) for database access. To
create the database tables, run these commands from the BroncoCast directory
on your web server:

`php bin/console doctrine:migrations:migrate`

`php bin/console bcast:setup`

The second command will ask you for a few pieces of information then configure
your database tables with an initial organization and system admin user.

## Web Server Setup

Symfony requires some specific configuration on your web server.  The Symfony
web site contains detailed instruction
[here](https://symfony.com/doc/current/setup/web_server_configuration.html).
Detailed instruction on installing and deploying Symfony are located
[here](https://symfony.com/doc/current/deployment.html).

## Cron Job

BroncoCast depends on a periodic cron job to deliver broadcasts and perform
routine maintenance.  It is recommended this job run every five minutes.

While running, it creates a lock file to prevent more than one instance from
running at a time.  If multiple consecutive attempts are made to run the job
while the lock file is present, the admin will receive an email and no
further attempts will be made to run the job or notify the admin of a problem.
