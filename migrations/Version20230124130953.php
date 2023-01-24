<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230124130953 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_chapter DROP INDEX UNIQ_F1086312DD524FD8, ADD INDEX IDX_F1086312DD524FD8 (related_chapter_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_chapter DROP INDEX IDX_F1086312DD524FD8, ADD UNIQUE INDEX UNIQ_F1086312DD524FD8 (related_chapter_id)');
    }
}
