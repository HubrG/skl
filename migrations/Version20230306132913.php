<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230306132913 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA20876202');
        $this->addSql('DROP INDEX IDX_BF5476CA20876202 ON notification');
        $this->addSql('ALTER TABLE notification DROP chapter_bookmark_id');
        //
        $this->addSql('DROP TABLE IF EXISTS publication_chapter_bookmark');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD chapter_bookmark_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA20876202 FOREIGN KEY (chapter_bookmark_id) REFERENCES publication_chapter_bookmark (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_BF5476CA20876202 ON notification (chapter_bookmark_id)');
        // 
    }
}
