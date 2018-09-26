<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180926142847 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE attachments (id INT AUTO_INCREMENT NOT NULL, broadcast_id INT NOT NULL, local_name VARCHAR(255) NOT NULL, friendly_name VARCHAR(255) NOT NULL, mime_type VARCHAR(255) NOT NULL, rev INT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_47C4FAD69C7E80E0 (broadcast_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE broadcasts (id INT AUTO_INCREMENT NOT NULL, org_id INT NOT NULL, usr_name VARCHAR(30) NOT NULL, scheduled DATETIME DEFAULT NULL, short_msg VARCHAR(140) NOT NULL, long_msg VARCHAR(2048) DEFAULT NULL, rev INT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, is_sent TINYINT(1) NOT NULL, INDEX IDX_D64238E4F4837C1B (org_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contacts (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, contact VARCHAR(50) NOT NULL, rev INT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, UNIQUE INDEX UNIQ_334015734C62E638 (contact), INDEX IDX_33401573A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groups (id INT AUTO_INCREMENT NOT NULL, org_id INT NOT NULL, grp_name VARCHAR(30) NOT NULL, rev INT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_F06D3970F4837C1B (org_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE grp_members (id INT AUTO_INCREMENT NOT NULL, grp_id INT NOT NULL, user_id INT NOT NULL, rev INT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_44BD48CCD51E9150 (grp_id), INDEX IDX_44BD48CCA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE org_members (id INT AUTO_INCREMENT NOT NULL, org_id INT NOT NULL, user_id INT NOT NULL, is_admin TINYINT(1) NOT NULL, is_approved TINYINT(1) NOT NULL, alt_usr_name VARCHAR(30) DEFAULT NULL, rev INT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, is_hidden TINYINT(1) NOT NULL, INDEX IDX_36DF8631F4837C1B (org_id), INDEX IDX_36DF8631A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE orgs (id INT AUTO_INCREMENT NOT NULL, org_name VARCHAR(30) NOT NULL, default_tz VARCHAR(50) NOT NULL, tag VARCHAR(15) NOT NULL, max_brc_age SMALLINT NOT NULL, rev INT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipients (id INT AUTO_INCREMENT NOT NULL, broadcast_id INT NOT NULL, user_id INT NOT NULL, rev INT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_146632C49C7E80E0 (broadcast_id), INDEX IDX_146632C4A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sms_logs (id INT AUTO_INCREMENT NOT NULL, contact_id INT NOT NULL, code VARCHAR(255) NOT NULL, message VARCHAR(255) NOT NULL, rev INT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_4BA25A0AE7A1254A (contact_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, password VARCHAR(64) NOT NULL, legacy_password VARCHAR(40) NOT NULL, salt VARCHAR(10) NOT NULL, is_active TINYINT(1) NOT NULL, fullname VARCHAR(30) NOT NULL, single_msg TINYINT(1) NOT NULL, rev INT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, reset_str VARCHAR(64) DEFAULT NULL, reset_expire DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9193D266D (reset_str), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attachments ADD CONSTRAINT FK_47C4FAD69C7E80E0 FOREIGN KEY (broadcast_id) REFERENCES broadcasts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE broadcasts ADD CONSTRAINT FK_D64238E4F4837C1B FOREIGN KEY (org_id) REFERENCES orgs (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contacts ADD CONSTRAINT FK_33401573A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groups ADD CONSTRAINT FK_F06D3970F4837C1B FOREIGN KEY (org_id) REFERENCES orgs (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE grp_members ADD CONSTRAINT FK_44BD48CCD51E9150 FOREIGN KEY (grp_id) REFERENCES groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE grp_members ADD CONSTRAINT FK_44BD48CCA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE org_members ADD CONSTRAINT FK_36DF8631F4837C1B FOREIGN KEY (org_id) REFERENCES orgs (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE org_members ADD CONSTRAINT FK_36DF8631A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipients ADD CONSTRAINT FK_146632C49C7E80E0 FOREIGN KEY (broadcast_id) REFERENCES broadcasts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipients ADD CONSTRAINT FK_146632C4A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sms_logs ADD CONSTRAINT FK_4BA25A0AE7A1254A FOREIGN KEY (contact_id) REFERENCES contacts (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE attachments DROP FOREIGN KEY FK_47C4FAD69C7E80E0');
        $this->addSql('ALTER TABLE recipients DROP FOREIGN KEY FK_146632C49C7E80E0');
        $this->addSql('ALTER TABLE sms_logs DROP FOREIGN KEY FK_4BA25A0AE7A1254A');
        $this->addSql('ALTER TABLE grp_members DROP FOREIGN KEY FK_44BD48CCD51E9150');
        $this->addSql('ALTER TABLE broadcasts DROP FOREIGN KEY FK_D64238E4F4837C1B');
        $this->addSql('ALTER TABLE groups DROP FOREIGN KEY FK_F06D3970F4837C1B');
        $this->addSql('ALTER TABLE org_members DROP FOREIGN KEY FK_36DF8631F4837C1B');
        $this->addSql('ALTER TABLE contacts DROP FOREIGN KEY FK_33401573A76ED395');
        $this->addSql('ALTER TABLE grp_members DROP FOREIGN KEY FK_44BD48CCA76ED395');
        $this->addSql('ALTER TABLE org_members DROP FOREIGN KEY FK_36DF8631A76ED395');
        $this->addSql('ALTER TABLE recipients DROP FOREIGN KEY FK_146632C4A76ED395');
        $this->addSql('DROP TABLE attachments');
        $this->addSql('DROP TABLE broadcasts');
        $this->addSql('DROP TABLE contacts');
        $this->addSql('DROP TABLE groups');
        $this->addSql('DROP TABLE grp_members');
        $this->addSql('DROP TABLE org_members');
        $this->addSql('DROP TABLE orgs');
        $this->addSql('DROP TABLE recipients');
        $this->addSql('DROP TABLE sms_logs');
        $this->addSql('DROP TABLE users');
    }
}
