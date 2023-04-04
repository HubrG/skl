<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230331110814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_parameters CHANGE notif_1_mail notif_1_mail TINYINT(1) DEFAULT 1, CHANGE notif_1_web notif_1_web TINYINT(1) DEFAULT 1, CHANGE notif_2_mail notif_2_mail TINYINT(1) DEFAULT 1, CHANGE notif_2_web notif_2_web TINYINT(1) DEFAULT 1, CHANGE notif_3_mail notif_3_mail TINYINT(1) DEFAULT 1, CHANGE notif_3_web notif_3_web TINYINT(1) DEFAULT 1, CHANGE notif_4_mail notif_4_mail TINYINT(1) DEFAULT 1, CHANGE notif_4_web notif_4_web TINYINT(1) DEFAULT 1, CHANGE notif_5_mail notif_5_mail TINYINT(1) DEFAULT 1, CHANGE notif_5_web notif_5_web TINYINT(1) DEFAULT 1, CHANGE notif_6_mail notif_6_mail TINYINT(1) DEFAULT 1, CHANGE notif_6_web notif_6_web TINYINT(1) DEFAULT 1, CHANGE notif_7_mail notif_7_mail TINYINT(1) DEFAULT 1, CHANGE notif_7_web notif_7_web TINYINT(1) DEFAULT 1, CHANGE notif_8_mail notif_8_mail TINYINT(1) DEFAULT 1, CHANGE notif_8_web notif_8_web TINYINT(1) DEFAULT 1, CHANGE notif_9_mail notif_9_mail TINYINT(1) DEFAULT 1, CHANGE notif_9_web notif_9_web TINYINT(1) DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_parameters CHANGE notif_1_mail notif_1_mail TINYINT(1) DEFAULT NULL, CHANGE notif_1_web notif_1_web TINYINT(1) DEFAULT NULL, CHANGE notif_2_mail notif_2_mail TINYINT(1) DEFAULT NULL, CHANGE notif_2_web notif_2_web TINYINT(1) DEFAULT NULL, CHANGE notif_3_mail notif_3_mail TINYINT(1) DEFAULT NULL, CHANGE notif_3_web notif_3_web TINYINT(1) DEFAULT NULL, CHANGE notif_4_mail notif_4_mail TINYINT(1) DEFAULT NULL, CHANGE notif_4_web notif_4_web TINYINT(1) DEFAULT NULL, CHANGE notif_5_mail notif_5_mail TINYINT(1) DEFAULT NULL, CHANGE notif_5_web notif_5_web TINYINT(1) DEFAULT NULL, CHANGE notif_6_mail notif_6_mail TINYINT(1) DEFAULT NULL, CHANGE notif_6_web notif_6_web TINYINT(1) DEFAULT NULL, CHANGE notif_7_mail notif_7_mail TINYINT(1) DEFAULT NULL, CHANGE notif_7_web notif_7_web TINYINT(1) DEFAULT NULL, CHANGE notif_8_mail notif_8_mail TINYINT(1) DEFAULT NULL, CHANGE notif_8_web notif_8_web TINYINT(1) DEFAULT NULL, CHANGE notif_9_mail notif_9_mail TINYINT(1) DEFAULT NULL, CHANGE notif_9_web notif_9_web TINYINT(1) DEFAULT NULL');
    }
}
