<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230303121210 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE publication_follow (id INT AUTO_INCREMENT NOT NULL, publication_id INT DEFAULT NULL, user_id INT DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8CDF553D38B217A7 (publication_id), INDEX IDX_8CDF553DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE publication_follow ADD CONSTRAINT FK_8CDF553D38B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id)');
        $this->addSql('ALTER TABLE publication_follow ADD CONSTRAINT FK_8CDF553DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_follow DROP FOREIGN KEY FK_8CDF553D38B217A7');
        $this->addSql('ALTER TABLE publication_follow DROP FOREIGN KEY FK_8CDF553DA76ED395');
        $this->addSql('DROP TABLE publication_follow');
    }
}
