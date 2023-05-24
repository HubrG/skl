<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230524101955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_parameters ADD notif_12_mail TINYINT(1) DEFAULT 1, ADD notif_12_web TINYINT(1) DEFAULT 1, ADD notif_13_mail TINYINT(1) DEFAULT 1, ADD notif_13_web TINYINT(1) DEFAULT 1, ADD notif_14_mail TINYINT(1) DEFAULT 1, ADD notif_14_web TINYINT(1) DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_parameters DROP notif_12_mail, DROP notif_12_web, DROP notif_13_mail, DROP notif_13_web, DROP notif_14_mail, DROP notif_14_web');
    }
}
