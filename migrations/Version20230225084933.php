<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230225084933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_bookmark ADD chapter_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE publication_bookmark ADD CONSTRAINT FK_F505674C579F4768 FOREIGN KEY (chapter_id) REFERENCES publication_chapter (id)');
        $this->addSql('CREATE INDEX IDX_F505674C579F4768 ON publication_bookmark (chapter_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_bookmark DROP FOREIGN KEY FK_F505674C579F4768');
        $this->addSql('DROP INDEX IDX_F505674C579F4768 ON publication_bookmark');
        $this->addSql('ALTER TABLE publication_bookmark DROP chapter_id');
    }
}
