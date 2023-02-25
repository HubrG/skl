<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230224191856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_comment ADD reply_to_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE publication_comment ADD CONSTRAINT FK_9CFD8450FFDF7169 FOREIGN KEY (reply_to_id) REFERENCES publication_comment (id)');
        $this->addSql('CREATE INDEX IDX_9CFD8450FFDF7169 ON publication_comment (reply_to_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_comment DROP FOREIGN KEY FK_9CFD8450FFDF7169');
        $this->addSql('DROP INDEX IDX_9CFD8450FFDF7169 ON publication_comment');
        $this->addSql('ALTER TABLE publication_comment DROP reply_to_id');
    }
}
