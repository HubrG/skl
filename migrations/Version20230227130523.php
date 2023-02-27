<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230227130523 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD bookmark_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA92741D25 FOREIGN KEY (bookmark_id) REFERENCES publication_chapter_bookmark (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA92741D25 ON notification (bookmark_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA92741D25');
        $this->addSql('DROP INDEX IDX_BF5476CA92741D25 ON notification');
        $this->addSql('ALTER TABLE notification DROP bookmark_id');
    }
}
