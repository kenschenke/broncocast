<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190705144731 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE auth');
        $this->addSql('DROP TABLE brc');
        $this->addSql('DROP TABLE brca');
        $this->addSql('DROP TABLE brcr');
        $this->addSql('DROP TABLE car');
        $this->addSql('DROP TABLE grp');
        $this->addSql('DROP TABLE grpm');
        $this->addSql('DROP TABLE org');
        $this->addSql('DROP TABLE orgm');
        $this->addSql('DROP TABLE usr');
        $this->addSql('DROP TABLE usrc');
        $this->addSql('ALTER TABLE contacts CHANGE contact contact VARCHAR(175) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE auth (AuthId BIGINT AUTO_INCREMENT NOT NULL, UsrId BIGINT NOT NULL, Token CHAR(40) NOT NULL COLLATE utf8_general_ci, Expires DATETIME NOT NULL, Updated DATETIME NOT NULL, Created DATETIME NOT NULL, Rev SMALLINT NOT NULL, INDEX UsrId (UsrId), UNIQUE INDEX AUTH_IX1 (Token), PRIMARY KEY(AuthId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE brc (BrcId BIGINT AUTO_INCREMENT NOT NULL, OrgId BIGINT NOT NULL, UsrName VARCHAR(30) NOT NULL COLLATE utf8_general_ci, Scheduled DATETIME DEFAULT NULL, ShortMsg VARCHAR(140) DEFAULT NULL COLLATE utf8_general_ci, LongMsg VARCHAR(2048) DEFAULT NULL COLLATE utf8_general_ci, Updated DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, Created DATETIME NOT NULL, Rev SMALLINT NOT NULL, INDEX BRC_IX1 (OrgId), PRIMARY KEY(BrcId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE brca (BrcAId BIGINT AUTO_INCREMENT NOT NULL, BrcId BIGINT NOT NULL, LocalName VARCHAR(255) NOT NULL COLLATE utf8_general_ci, FriendlyName VARCHAR(255) NOT NULL COLLATE utf8_general_ci, MimeType VARCHAR(255) NOT NULL COLLATE utf8_general_ci, Updated DATETIME DEFAULT NULL, Created DATETIME NOT NULL, Rev SMALLINT NOT NULL, INDEX BRCA_IX1 (BrcId), PRIMARY KEY(BrcAId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE brcr (BrcRId BIGINT AUTO_INCREMENT NOT NULL, BrcId BIGINT NOT NULL, UsrCId BIGINT NOT NULL, Sent DATETIME DEFAULT NULL, Updated DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, Created DATETIME NOT NULL, Rev SMALLINT NOT NULL, INDEX BRCR_IX1 (BrcId), INDEX UsrCId (UsrCId), PRIMARY KEY(BrcRId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE car (CarId BIGINT AUTO_INCREMENT NOT NULL, CarName VARCHAR(30) NOT NULL COLLATE utf8_general_ci, Gateway VARCHAR(50) NOT NULL COLLATE utf8_general_ci, Updated DATETIME NOT NULL, Created DATETIME NOT NULL, Rev SMALLINT NOT NULL, UNIQUE INDEX CAR_IX1 (CarName), PRIMARY KEY(CarId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE grp (GrpId BIGINT AUTO_INCREMENT NOT NULL, GrpName VARCHAR(30) NOT NULL COLLATE utf8_general_ci, OrgId BIGINT NOT NULL, Updated DATETIME NOT NULL, Created DATETIME NOT NULL, Rev SMALLINT NOT NULL, UNIQUE INDEX GRP_IX1 (OrgId, GrpName), PRIMARY KEY(GrpId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE grpm (GrpMId BIGINT AUTO_INCREMENT NOT NULL, GrpId BIGINT NOT NULL, UsrId BIGINT NOT NULL, Updated DATETIME NOT NULL, Created DATETIME NOT NULL, Rev SMALLINT NOT NULL, INDEX UsrId (UsrId), UNIQUE INDEX GRPM_IX1 (GrpId, UsrId), PRIMARY KEY(GrpMId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE org (OrgId BIGINT AUTO_INCREMENT NOT NULL, OrgName VARCHAR(30) NOT NULL COLLATE utf8_general_ci, DefaultTZ VARCHAR(50) NOT NULL COLLATE utf8_general_ci, Tag VARCHAR(15) NOT NULL COLLATE utf8_general_ci, MaxBRCAge SMALLINT NOT NULL, Updated DATETIME NOT NULL, Created DATETIME NOT NULL, Rev SMALLINT NOT NULL, UNIQUE INDEX ORG_IX1 (OrgName), PRIMARY KEY(OrgId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE orgm (OrgMId BIGINT AUTO_INCREMENT NOT NULL, OrgId BIGINT NOT NULL, UsrId BIGINT NOT NULL, IsAdmin TINYINT(1) NOT NULL, Approved TINYINT(1) NOT NULL, Blocked TINYINT(1) NOT NULL, AltUsrName VARCHAR(30) DEFAULT NULL COLLATE utf8_general_ci, Updated DATETIME NOT NULL, Created DATETIME NOT NULL, Rev SMALLINT NOT NULL, INDEX UsrId (UsrId), UNIQUE INDEX ORGM_IX1 (OrgId, UsrId), PRIMARY KEY(OrgMId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE usr (UsrId BIGINT AUTO_INCREMENT NOT NULL, UsrName VARCHAR(30) NOT NULL COLLATE utf8_general_ci, Password CHAR(40) NOT NULL COLLATE utf8_general_ci, Salt CHAR(10) NOT NULL COLLATE utf8_general_ci, Failures SMALLINT NOT NULL, ResetStr CHAR(40) DEFAULT NULL COLLATE utf8_general_ci, ResetExpire DATETIME DEFAULT NULL, SingleMsg TINYINT(1) NOT NULL, Updated DATETIME NOT NULL, Created DATETIME NOT NULL, Rev SMALLINT NOT NULL, UNIQUE INDEX USR_IX1 (ResetStr), PRIMARY KEY(UsrId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE usrc (UsrCId BIGINT AUTO_INCREMENT NOT NULL, UsrId BIGINT NOT NULL, Contact VARCHAR(50) NOT NULL COLLATE utf8_general_ci, CarId BIGINT DEFAULT NULL, IsConfirmed TINYINT(1) NOT NULL, Updated DATETIME NOT NULL, Created DATETIME NOT NULL, Rev SMALLINT NOT NULL, INDEX CarId (CarId), INDEX USRC_IX2 (UsrId), UNIQUE INDEX USRC_IX1 (Contact), PRIMARY KEY(UsrCId)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('ALTER TABLE contacts CHANGE contact contact VARCHAR(150) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
