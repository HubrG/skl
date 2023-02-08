<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230208190402 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_chapter_note ADD publication_chapter_comment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE publication_chapter_note ADD CONSTRAINT FK_A29F9744DF71F9DE FOREIGN KEY (publication_chapter_comment_id) REFERENCES publication_chapter_comment (id)');
        $this->addSql('CREATE INDEX IDX_A29F9744DF71F9DE ON publication_chapter_note (publication_chapter_comment_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_chapter_note DROP FOREIGN KEY FK_A29F9744DF71F9DE');
        $this->addSql('DROP INDEX IDX_A29F9744DF71F9DE ON publication_chapter_note');
        $this->addSql('ALTER TABLE publication_chapter_note DROP publication_chapter_comment_id');
    }
}
