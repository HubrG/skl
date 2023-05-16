<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230516150933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inbox_group_member (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, grouped_id INT DEFAULT NULL, INDEX IDX_CD3E3502A76ED395 (user_id), INDEX IDX_CD3E3502CECB583D (grouped_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inbox_group_member ADD CONSTRAINT FK_CD3E3502A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE inbox_group_member ADD CONSTRAINT FK_CD3E3502CECB583D FOREIGN KEY (grouped_id) REFERENCES inbox_group (id)');
        $this->addSql('ALTER TABLE inbox ADD grouped_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inbox ADD CONSTRAINT FK_7E11F339CECB583D FOREIGN KEY (grouped_id) REFERENCES inbox_group (id)');
        $this->addSql('CREATE INDEX IDX_7E11F339CECB583D ON inbox (grouped_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inbox_group_member DROP FOREIGN KEY FK_CD3E3502A76ED395');
        $this->addSql('ALTER TABLE inbox_group_member DROP FOREIGN KEY FK_CD3E3502CECB583D');
        $this->addSql('DROP TABLE inbox_group_member');
        $this->addSql('ALTER TABLE inbox DROP FOREIGN KEY FK_7E11F339CECB583D');
        $this->addSql('DROP INDEX IDX_7E11F339CECB583D ON inbox');
        $this->addSql('ALTER TABLE inbox DROP grouped_id');
    }
}
