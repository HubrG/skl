<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230524085029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD assign_forum_message_id INT DEFAULT NULL, ADD assign_comment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAD6FF8939 FOREIGN KEY (assign_forum_message_id) REFERENCES forum_message (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CABCEEF530 FOREIGN KEY (assign_comment_id) REFERENCES publication_comment (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CAD6FF8939 ON notification (assign_forum_message_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CABCEEF530 ON notification (assign_comment_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAD6FF8939');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CABCEEF530');
        $this->addSql('DROP INDEX IDX_BF5476CAD6FF8939 ON notification');
        $this->addSql('DROP INDEX IDX_BF5476CABCEEF530 ON notification');
        $this->addSql('ALTER TABLE notification DROP assign_forum_message_id, DROP assign_comment_id');
    }
}
