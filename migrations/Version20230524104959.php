<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230524104959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD assign_forum_topic_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA60746C37 FOREIGN KEY (assign_forum_topic_id) REFERENCES forum_topic (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA60746C37 ON notification (assign_forum_topic_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA60746C37');
        $this->addSql('DROP INDEX IDX_BF5476CA60746C37 ON notification');
        $this->addSql('ALTER TABLE notification DROP assign_forum_topic_id');
    }
}
