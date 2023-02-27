<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230227124437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD like_comment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA5BFDDDEB FOREIGN KEY (like_comment_id) REFERENCES publication_comment_like (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA5BFDDDEB ON notification (like_comment_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA5BFDDDEB');
        $this->addSql('DROP INDEX IDX_BF5476CA5BFDDDEB ON notification');
        $this->addSql('ALTER TABLE notification DROP like_comment_id');
    }
}
