<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230530104638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD assign_forum_reply_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAF52F0275 FOREIGN KEY (assign_forum_reply_id) REFERENCES forum_message (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CAF52F0275 ON notification (assign_forum_reply_id)');
        $this->addSql('ALTER TABLE user_parameters ADD notif_17_mail TINYINT(1) DEFAULT 1, ADD notif_17_web TINYINT(1) DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAF52F0275');
        $this->addSql('DROP INDEX IDX_BF5476CAF52F0275 ON notification');
        $this->addSql('ALTER TABLE notification DROP assign_forum_reply_id');
        $this->addSql('ALTER TABLE user_parameters DROP notif_17_mail, DROP notif_17_web');
    }
}
