<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190220163854 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contacts ADD contact_type VARCHAR(15) NOT NULL, CHANGE contact contact VARCHAR(150) NOT NULL');

        $this->addSql("UPDATE contacts SET contact_type = 'EMAIL' WHERE contact LIKE '%@%'");
        $this->addSql("UPDATE contacts SET contact_type = 'PHONE' WHERE contact NOT LIKE '%@%'");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contacts DROP contact_type, CHANGE contact contact VARCHAR(50) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
