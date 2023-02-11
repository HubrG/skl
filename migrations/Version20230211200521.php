<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230211200521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE publication_chapter_bookmark (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, chapter_id INT DEFAULT NULL, INDEX IDX_C5D19E09A76ED395 (user_id), INDEX IDX_C5D19E09579F4768 (chapter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE publication_chapter_bookmark ADD CONSTRAINT FK_C5D19E09A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE publication_chapter_bookmark ADD CONSTRAINT FK_C5D19E09579F4768 FOREIGN KEY (chapter_id) REFERENCES publication_chapter (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_chapter_bookmark DROP FOREIGN KEY FK_C5D19E09A76ED395');
        $this->addSql('ALTER TABLE publication_chapter_bookmark DROP FOREIGN KEY FK_C5D19E09579F4768');
        $this->addSql('DROP TABLE publication_chapter_bookmark');
    }
}
