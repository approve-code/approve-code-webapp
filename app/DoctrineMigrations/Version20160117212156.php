<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160117212156 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql(
            'CREATE TABLE ac_user_repository(
                id INT AUTO_INCREMENT NOT NULL,
                owner_id INT NOT NULL,
                github_id INT NOT NULL,
                full_name VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                webhook_id BIGINT DEFAULT NULL,
                UNIQUE INDEX UNIQ_250480EED4327649 (github_id),
                UNIQUE INDEX UNIQ_250480EEDBC463C4 (full_name),
                INDEX IDX_250480EE7E3C61F9 (owner_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB'
        );

        $this->addSql(
            'CREATE TABLE ac_user (
                id INT AUTO_INCREMENT NOT NULL,
                username VARCHAR(255) NOT NULL,
                username_canonical VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                email_canonical VARCHAR(255) NOT NULL,
                enabled TINYINT(1) NOT NULL,
                salt VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                last_login DATETIME DEFAULT NULL,
                locked TINYINT(1) NOT NULL,
                expired TINYINT(1) NOT NULL,
                expires_at DATETIME DEFAULT NULL,
                confirmation_token VARCHAR(255) DEFAULT NULL,
                password_requested_at DATETIME DEFAULT NULL,
                roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\',
                credentials_expired TINYINT(1) NOT NULL,
                credentials_expire_at DATETIME DEFAULT NULL,
                github_id VARCHAR(255) DEFAULT NULL,
                access_token VARCHAR(255) NOT NULL,
                UNIQUE INDEX UNIQ_2859B49492FC23A8 (username_canonical),
                UNIQUE INDEX UNIQ_2859B494A0D96FBF (email_canonical),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB'
        );

        $this->addSql(
            'ALTER TABLE ac_user_repository
                ADD CONSTRAINT FK_250480EE7E3C61F9 FOREIGN KEY (owner_id) REFERENCES ac_user (id)'
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE ac_user_repository DROP FOREIGN KEY FK_250480EE7E3C61F9');
        $this->addSql('DROP TABLE ac_user_repository');
        $this->addSql('DROP TABLE ac_user');
    }
}
