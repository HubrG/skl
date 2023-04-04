<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230331104259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_parameters ADD notif_1_mail TINYINT(1) NOT NULL, ADD notif_1_web TINYINT(1) DEFAULT NULL, ADD notif_2_mail TINYINT(1) DEFAULT NULL, ADD notif_2_web TINYINT(1) DEFAULT NULL, ADD notif_3_mail TINYINT(1) DEFAULT NULL, ADD notif_3_web TINYINT(1) DEFAULT NULL, ADD notif_4_mail TINYINT(1) DEFAULT NULL, ADD notif_4_web TINYINT(1) DEFAULT NULL, ADD notif_5_mail TINYINT(1) DEFAULT NULL, ADD notif_5_web TINYINT(1) DEFAULT NULL, ADD notif_6_mail TINYINT(1) DEFAULT NULL, ADD notif_6_web TINYINT(1) DEFAULT NULL, ADD notif_7_mail TINYINT(1) DEFAULT NULL, ADD notif_7_web TINYINT(1) DEFAULT NULL, ADD notif_8_mail TINYINT(1) DEFAULT NULL, ADD notif_8_web TINYINT(1) DEFAULT NULL, ADD notif_9_mail TINYINT(1) DEFAULT NULL, ADD notif_9_web TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_parameters DROP notif_1_mail, DROP notif_1_web, DROP notif_2_mail, DROP notif_2_web, DROP notif_3_mail, DROP notif_3_web, DROP notif_4_mail, DROP notif_4_web, DROP notif_5_mail, DROP notif_5_web, DROP notif_6_mail, DROP notif_6_web, DROP notif_7_mail, DROP notif_7_web, DROP notif_8_mail, DROP notif_8_web, DROP notif_9_mail, DROP notif_9_web');
    }
}
