<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230614144047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD publication_chapter_bookmark_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA7786BBAC FOREIGN KEY (publication_chapter_bookmark_id) REFERENCES publication_bookmark (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA7786BBAC ON notification (publication_chapter_bookmark_id)');
        $this->addSql('ALTER TABLE user_parameters ADD notif_26_web TINYINT(1) DEFAULT 1, ADD notif_26_mail TINYINT(1) DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA7786BBAC');
        $this->addSql('DROP INDEX IDX_BF5476CA7786BBAC ON notification');
        $this->addSql('ALTER TABLE notification DROP publication_chapter_bookmark_id');
        $this->addSql('ALTER TABLE user_parameters DROP notif_26_web, DROP notif_26_mail');
    }
}
