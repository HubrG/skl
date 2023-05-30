<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230530040325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE forum_message ADD reply_to_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE forum_message ADD CONSTRAINT FK_47717D0EFFDF7169 FOREIGN KEY (reply_to_id) REFERENCES forum_message (id)');
        $this->addSql('CREATE INDEX IDX_47717D0EFFDF7169 ON forum_message (reply_to_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE forum_message DROP FOREIGN KEY FK_47717D0EFFDF7169');
        $this->addSql('DROP INDEX IDX_47717D0EFFDF7169 ON forum_message');
        $this->addSql('ALTER TABLE forum_message DROP reply_to_id');
    }
}
