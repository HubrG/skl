<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230208190237 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_chapter_note DROP FOREIGN KEY FK_A29F9744F8697D13');
        $this->addSql('DROP INDEX IDX_A29F9744F8697D13 ON publication_chapter_note');
        $this->addSql('ALTER TABLE publication_chapter_note DROP comment_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_chapter_note ADD comment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE publication_chapter_note ADD CONSTRAINT FK_A29F9744F8697D13 FOREIGN KEY (comment_id) REFERENCES publication_chapter_comment (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_A29F9744F8697D13 ON publication_chapter_note (comment_id)');
    }
}
