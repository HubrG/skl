<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230227132341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD download_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAC667AEAB FOREIGN KEY (download_id) REFERENCES publication_download (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CAC667AEAB ON notification (download_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAC667AEAB');
        $this->addSql('DROP INDEX IDX_BF5476CAC667AEAB ON notification');
        $this->addSql('ALTER TABLE notification DROP download_id');
    }
}
