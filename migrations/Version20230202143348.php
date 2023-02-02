<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230202143348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE publication_chapter_comment (id INT AUTO_INCREMENT NOT NULL, chapter_id INT DEFAULT NULL, user_id INT DEFAULT NULL, content LONGTEXT DEFAULT NULL, publish_date DATETIME DEFAULT NULL, INDEX IDX_FDFB28BF579F4768 (chapter_id), INDEX IDX_FDFB28BFA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE publication_chapter_comment ADD CONSTRAINT FK_FDFB28BF579F4768 FOREIGN KEY (chapter_id) REFERENCES publication_chapter (id)');
        $this->addSql('ALTER TABLE publication_chapter_comment ADD CONSTRAINT FK_FDFB28BFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_chapter_comment DROP FOREIGN KEY FK_FDFB28BF579F4768');
        $this->addSql('ALTER TABLE publication_chapter_comment DROP FOREIGN KEY FK_FDFB28BFA76ED395');
        $this->addSql('DROP TABLE publication_chapter_comment');
    }
}
