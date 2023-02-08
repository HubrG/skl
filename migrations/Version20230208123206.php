<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230208123206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_chapter_comment ADD note_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE publication_chapter_comment ADD CONSTRAINT FK_FDFB28BF26ED0855 FOREIGN KEY (note_id) REFERENCES publication_chapter_note (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FDFB28BF26ED0855 ON publication_chapter_comment (note_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_chapter_comment DROP FOREIGN KEY FK_FDFB28BF26ED0855');
        $this->addSql('DROP INDEX UNIQ_FDFB28BF26ED0855 ON publication_chapter_comment');
        $this->addSql('ALTER TABLE publication_chapter_comment DROP note_id');
    }
}
