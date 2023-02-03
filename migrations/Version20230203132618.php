<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230203132618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_chapter_comment_like ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE publication_chapter_comment_like ADD CONSTRAINT FK_1ED42B7DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_1ED42B7DA76ED395 ON publication_chapter_comment_like (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_chapter_comment_like DROP FOREIGN KEY FK_1ED42B7DA76ED395');
        $this->addSql('DROP INDEX IDX_1ED42B7DA76ED395 ON publication_chapter_comment_like');
        $this->addSql('ALTER TABLE publication_chapter_comment_like DROP user_id');
    }
}
