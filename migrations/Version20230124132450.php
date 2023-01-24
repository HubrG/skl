<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230124132450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE publication_chapter_versioning (id INT AUTO_INCREMENT NOT NULL, chapter_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, content LONGTEXT DEFAULT NULL, created DATETIME DEFAULT NULL, INDEX IDX_9A0AFD97579F4768 (chapter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE publication_chapter_versioning ADD CONSTRAINT FK_9A0AFD97579F4768 FOREIGN KEY (chapter_id) REFERENCES publication_chapter (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_chapter_versioning DROP FOREIGN KEY FK_9A0AFD97579F4768');
        $this->addSql('DROP TABLE publication_chapter_versioning');
    }
}
