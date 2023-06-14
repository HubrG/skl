<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230614090954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD challenge_message_id INT DEFAULT NULL, ADD challenge_response_id INT DEFAULT NULL, ADD assign_challenge_id INT DEFAULT NULL, ADD assign_challenge_message_id INT DEFAULT NULL, ADD like_challenge_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA336A0A20 FOREIGN KEY (challenge_message_id) REFERENCES challenge_message (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA824F80FD FOREIGN KEY (challenge_response_id) REFERENCES publication (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAC5682232 FOREIGN KEY (assign_challenge_id) REFERENCES challenge (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA337CD0D FOREIGN KEY (assign_challenge_message_id) REFERENCES challenge_message (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA9211710F FOREIGN KEY (like_challenge_id) REFERENCES challenge_message (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA336A0A20 ON notification (challenge_message_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA824F80FD ON notification (challenge_response_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CAC5682232 ON notification (assign_challenge_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CAA337CD0D ON notification (assign_challenge_message_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA9211710F ON notification (like_challenge_id)');
        $this->addSql('ALTER TABLE user_parameters ADD notif_20_mail TINYINT(1) DEFAULT 1, ADD notif_20_web TINYINT(1) DEFAULT 1, ADD notif_21_mail TINYINT(1) DEFAULT 1, ADD notif_21_web TINYINT(1) DEFAULT 1, ADD notif_22_mail TINYINT(1) DEFAULT 1, ADD notif_22_web TINYINT(1) DEFAULT 1, ADD notif_23_mail TINYINT(1) DEFAULT 1, ADD notif_23_web TINYINT(1) DEFAULT 1, ADD notif_24_mail TINYINT(1) DEFAULT 1, ADD notif_24_web TINYINT(1) DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA336A0A20');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA824F80FD');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAC5682232');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA337CD0D');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA9211710F');
        $this->addSql('DROP INDEX IDX_BF5476CA336A0A20 ON notification');
        $this->addSql('DROP INDEX IDX_BF5476CA824F80FD ON notification');
        $this->addSql('DROP INDEX IDX_BF5476CAC5682232 ON notification');
        $this->addSql('DROP INDEX IDX_BF5476CAA337CD0D ON notification');
        $this->addSql('DROP INDEX IDX_BF5476CA9211710F ON notification');
        $this->addSql('ALTER TABLE notification DROP challenge_message_id, DROP challenge_response_id, DROP assign_challenge_id, DROP assign_challenge_message_id, DROP like_challenge_id');
        $this->addSql('ALTER TABLE user_parameters DROP notif_20_mail, DROP notif_20_web, DROP notif_21_mail, DROP notif_21_web, DROP notif_22_mail, DROP notif_22_web, DROP notif_23_mail, DROP notif_23_web, DROP notif_24_mail, DROP notif_24_web');
    }
}
