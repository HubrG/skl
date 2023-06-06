<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230606110957 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD new_friend_id INT DEFAULT NULL, ADD friend_new_pub_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA58CB5D87 FOREIGN KEY (new_friend_id) REFERENCES user_follow (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAD5202C59 FOREIGN KEY (friend_new_pub_id) REFERENCES publication (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA58CB5D87 ON notification (new_friend_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CAD5202C59 ON notification (friend_new_pub_id)');
        $this->addSql('ALTER TABLE user_parameters ADD notif_18_mail TINYINT(1) DEFAULT 1, ADD notif_18_web TINYINT(1) DEFAULT 1, ADD notif_19_mail TINYINT(1) DEFAULT 1, ADD notif_19_web TINYINT(1) DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA58CB5D87');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAD5202C59');
        $this->addSql('DROP INDEX IDX_BF5476CA58CB5D87 ON notification');
        $this->addSql('DROP INDEX IDX_BF5476CAD5202C59 ON notification');
        $this->addSql('ALTER TABLE notification DROP new_friend_id, DROP friend_new_pub_id');
        $this->addSql('ALTER TABLE user_parameters DROP notif_18_mail, DROP notif_18_web, DROP notif_19_mail, DROP notif_19_web');
    }
}
