<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230306123244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD publication_bookmark_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA4517B88E FOREIGN KEY (publication_bookmark_id) REFERENCES publication_bookmark (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA4517B88E ON notification (publication_bookmark_id)');
    }
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA4517B88E');
        $this->addSql('DROP INDEX IDX_BF5476CA4517B88E ON notification');
        $this->addSql('ALTER TABLE notification DROP publication_bookmark_id');
    }
}
