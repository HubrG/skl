<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230530041416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE forum_message_like (id INT AUTO_INCREMENT NOT NULL, message_id INT DEFAULT NULL, user_id INT DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3D282D2D537A1329 (message_id), INDEX IDX_3D282D2DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE forum_message_like ADD CONSTRAINT FK_3D282D2D537A1329 FOREIGN KEY (message_id) REFERENCES forum_message (id)');
        $this->addSql('ALTER TABLE forum_message_like ADD CONSTRAINT FK_3D282D2DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE forum_message_like DROP FOREIGN KEY FK_3D282D2D537A1329');
        $this->addSql('ALTER TABLE forum_message_like DROP FOREIGN KEY FK_3D282D2DA76ED395');
        $this->addSql('DROP TABLE forum_message_like');
    }
}
