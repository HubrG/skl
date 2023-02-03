<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230203132308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE publication_chapter_comment_like (id INT AUTO_INCREMENT NOT NULL, comment_id INT DEFAULT NULL, INDEX IDX_1ED42B7DF8697D13 (comment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE publication_chapter_comment_like ADD CONSTRAINT FK_1ED42B7DF8697D13 FOREIGN KEY (comment_id) REFERENCES publication_chapter_comment (id)');
        $this->addSql('ALTER TABLE user ADD publication_chapter_comment_like_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649CDA6D589 FOREIGN KEY (publication_chapter_comment_like_id) REFERENCES publication_chapter_comment_like (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649CDA6D589 ON user (publication_chapter_comment_like_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649CDA6D589');
        $this->addSql('ALTER TABLE publication_chapter_comment_like DROP FOREIGN KEY FK_1ED42B7DF8697D13');
        $this->addSql('DROP TABLE publication_chapter_comment_like');
        $this->addSql('DROP INDEX IDX_8D93D649CDA6D589 ON user');
        $this->addSql('ALTER TABLE user DROP publication_chapter_comment_like_id');
    }
}
