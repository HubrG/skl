<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230426152317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_read ADD publication_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE publication_read ADD CONSTRAINT FK_93AFC0AA38B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id)');
        $this->addSql('CREATE INDEX IDX_93AFC0AA38B217A7 ON publication_read (publication_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_read DROP FOREIGN KEY FK_93AFC0AA38B217A7');
        $this->addSql('DROP INDEX IDX_93AFC0AA38B217A7 ON publication_read');
        $this->addSql('ALTER TABLE publication_read DROP publication_id');
    }
}
