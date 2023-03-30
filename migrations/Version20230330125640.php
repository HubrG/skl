<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230330125640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD reply_comment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAF2A47145 FOREIGN KEY (reply_comment_id) REFERENCES publication_comment (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CAF2A47145 ON notification (reply_comment_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAF2A47145');
        $this->addSql('DROP INDEX IDX_BF5476CAF2A47145 ON notification');
        $this->addSql('ALTER TABLE notification DROP reply_comment_id');
    }
}
