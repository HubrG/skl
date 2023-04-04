<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230227140108 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAF40BE790');
        $this->addSql('DROP INDEX IDX_BF5476CAF40BE790 ON notification');
        $this->addSql('ALTER TABLE notification CHANGE like_chapter_id chapter_like_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAFFAAFE7D FOREIGN KEY (chapter_like_id) REFERENCES publication_chapter_like (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CAFFAAFE7D ON notification (chapter_like_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAFFAAFE7D');
        $this->addSql('DROP INDEX IDX_BF5476CAFFAAFE7D ON notification');
        $this->addSql('ALTER TABLE notification CHANGE chapter_like_id like_chapter_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAF40BE790 FOREIGN KEY (like_chapter_id) REFERENCES publication_chapter_like (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_BF5476CAF40BE790 ON notification (like_chapter_id)');
    }
}
