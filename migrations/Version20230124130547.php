<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230124130547 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_chapter ADD related_chapter_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE publication_chapter ADD CONSTRAINT FK_F1086312DD524FD8 FOREIGN KEY (related_chapter_id) REFERENCES publication_chapter (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F1086312DD524FD8 ON publication_chapter (related_chapter_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_chapter DROP FOREIGN KEY FK_F1086312DD524FD8');
        $this->addSql('DROP INDEX UNIQ_F1086312DD524FD8 ON publication_chapter');
        $this->addSql('ALTER TABLE publication_chapter DROP related_chapter_id');
    }
}
