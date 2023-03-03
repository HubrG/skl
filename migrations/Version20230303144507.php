<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230303144507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD publication_follow_add_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAF3728BF9 FOREIGN KEY (publication_follow_add_id) REFERENCES publication (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CAF3728BF9 ON notification (publication_follow_add_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAF3728BF9');
        $this->addSql('DROP INDEX IDX_BF5476CAF3728BF9 ON notification');
        $this->addSql('ALTER TABLE notification DROP publication_follow_add_id');
    }
}
