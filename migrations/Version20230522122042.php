<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230522122042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD revision_comment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CADF16717B FOREIGN KEY (revision_comment_id) REFERENCES publication_annotation (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CADF16717B ON notification (revision_comment_id)');
        $this->addSql('ALTER TABLE user_parameters ADD notif_10_mail TINYINT(1) DEFAULT 1, ADD notif_10_web TINYINT(1) DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CADF16717B');
        $this->addSql('DROP INDEX IDX_BF5476CADF16717B ON notification');
        $this->addSql('ALTER TABLE notification DROP revision_comment_id');
        $this->addSql('ALTER TABLE user_parameters DROP notif_10_mail, DROP notif_10_web');
    }
}
