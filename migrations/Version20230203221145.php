<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230203221145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE publication_chapter_note (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, chapter_id INT DEFAULT NULL, selection LONGTEXT DEFAULT NULL, type INT DEFAULT NULL, INDEX IDX_A29F9744A76ED395 (user_id), INDEX IDX_A29F9744579F4768 (chapter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE publication_chapter_note ADD CONSTRAINT FK_A29F9744A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE publication_chapter_note ADD CONSTRAINT FK_A29F9744579F4768 FOREIGN KEY (chapter_id) REFERENCES publication_chapter (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_chapter_note DROP FOREIGN KEY FK_A29F9744A76ED395');
        $this->addSql('ALTER TABLE publication_chapter_note DROP FOREIGN KEY FK_A29F9744579F4768');
        $this->addSql('DROP TABLE publication_chapter_note');
    }
}
