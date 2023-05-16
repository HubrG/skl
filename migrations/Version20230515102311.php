<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230515102311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inbox (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, user_to_id INT DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', read_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', content LONGTEXT DEFAULT NULL, INDEX IDX_7E11F339A76ED395 (user_id), INDEX IDX_7E11F339D2F7B13D (user_to_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inbox ADD CONSTRAINT FK_7E11F339A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE inbox ADD CONSTRAINT FK_7E11F339D2F7B13D FOREIGN KEY (user_to_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inbox DROP FOREIGN KEY FK_7E11F339A76ED395');
        $this->addSql('ALTER TABLE inbox DROP FOREIGN KEY FK_7E11F339D2F7B13D');
        $this->addSql('DROP TABLE inbox');
    }
}
