<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230519100211 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inbox DROP FOREIGN KEY FK_7E11F339D2F7B13D');
        $this->addSql('DROP INDEX IDX_7E11F339D2F7B13D ON inbox');
        $this->addSql('ALTER TABLE inbox DROP user_to_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inbox ADD user_to_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inbox ADD CONSTRAINT FK_7E11F339D2F7B13D FOREIGN KEY (user_to_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_7E11F339D2F7B13D ON inbox (user_to_id)');
    }
}
