<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230612083352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE challenge_message (id INT AUTO_INCREMENT NOT NULL, reply_to_id INT NOT NULL, user_id INT DEFAULT NULL, challenge_id INT DEFAULT NULL, content LONGTEXT DEFAULT NULL, published_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_5A2D7FFAA76ED395 (user_id), INDEX IDX_5A2D7FFA98A21AC6 (challenge_id), INDEX IDX_5A2D7FFAFFDF7169 (reply_to_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE challenge_message ADD CONSTRAINT FK_5A2D7FFAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE challenge_message ADD CONSTRAINT FK_5A2D7FFA98A21AC6 FOREIGN KEY (challenge_id) REFERENCES challenge (id)');
        $this->addSql('ALTER TABLE challenge_message ADD CONSTRAINT FK_5A2D7FFAFFDF7169 FOREIGN KEY (reply_to_id) REFERENCES challenge_message (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE challenge_message DROP FOREIGN KEY FK_5A2D7FFAA76ED395');
        $this->addSql('ALTER TABLE challenge_message DROP FOREIGN KEY FK_5A2D7FFA98A21AC6');
        $this->addSql('ALTER TABLE challenge_message DROP FOREIGN KEY FK_5A2D7FFAFFDF7169');
        $this->addSql('DROP TABLE challenge_message');
    }
}
