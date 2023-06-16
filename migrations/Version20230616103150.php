<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230616103150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE publication_annotation_reply (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, annotation_id INT DEFAULT NULL, content LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_EED45118A76ED395 (user_id), INDEX IDX_EED45118E075FC54 (annotation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE publication_annotation_reply ADD CONSTRAINT FK_EED45118A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE publication_annotation_reply ADD CONSTRAINT FK_EED45118E075FC54 FOREIGN KEY (annotation_id) REFERENCES publication_annotation (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_annotation_reply DROP FOREIGN KEY FK_EED45118A76ED395');
        $this->addSql('ALTER TABLE publication_annotation_reply DROP FOREIGN KEY FK_EED45118E075FC54');
        $this->addSql('DROP TABLE publication_annotation_reply');
    }
}
