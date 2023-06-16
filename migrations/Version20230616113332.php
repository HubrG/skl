<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230616113332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD annotation_reply_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA16B9460 FOREIGN KEY (annotation_reply_id) REFERENCES publication_annotation_reply (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA16B9460 ON notification (annotation_reply_id)');
        $this->addSql('ALTER TABLE user_parameters ADD notif_27_web TINYINT(1) DEFAULT 1, ADD notif_27_mail TINYINT(1) DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA16B9460');
        $this->addSql('DROP INDEX IDX_BF5476CA16B9460 ON notification');
        $this->addSql('ALTER TABLE notification DROP annotation_reply_id');
        $this->addSql('ALTER TABLE user_parameters DROP notif_27_web, DROP notif_27_mail');
    }
}
