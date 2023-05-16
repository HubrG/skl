<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230516150352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inbox_group DROP FOREIGN KEY FK_7DD3A111A76ED395');
        $this->addSql('DROP INDEX IDX_7DD3A111A76ED395 ON inbox_group');
        $this->addSql('ALTER TABLE inbox_group DROP user_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inbox_group ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inbox_group ADD CONSTRAINT FK_7DD3A111A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_7DD3A111A76ED395 ON inbox_group (user_id)');
    }
}
