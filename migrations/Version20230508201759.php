<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230508201759 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_annotation ADD version_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE publication_annotation ADD CONSTRAINT FK_40337B134BBC2705 FOREIGN KEY (version_id) REFERENCES publication_chapter_versioning (id)');
        $this->addSql('CREATE INDEX IDX_40337B134BBC2705 ON publication_annotation (version_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_annotation DROP FOREIGN KEY FK_40337B134BBC2705');
        $this->addSql('DROP INDEX IDX_40337B134BBC2705 ON publication_annotation');
        $this->addSql('ALTER TABLE publication_annotation DROP version_id');
    }
}
