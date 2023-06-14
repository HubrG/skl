<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230614101150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD challenge_message_reply_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAD119B2EA FOREIGN KEY (challenge_message_reply_id) REFERENCES challenge_message (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CAD119B2EA ON notification (challenge_message_reply_id)');
        $this->addSql('ALTER TABLE user_parameters ADD notif_25_web TINYINT(1) DEFAULT 1, ADD notif_25_mail TINYINT(1) DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAD119B2EA');
        $this->addSql('DROP INDEX IDX_BF5476CAD119B2EA ON notification');
        $this->addSql('ALTER TABLE notification DROP challenge_message_reply_id');
        $this->addSql('ALTER TABLE user_parameters DROP notif_25_web, DROP notif_25_mail');
    }
}
