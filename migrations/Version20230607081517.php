<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230607081517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE publication_access (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, publication_id INT DEFAULT NULL, INDEX IDX_E2823A19A76ED395 (user_id), INDEX IDX_E2823A1938B217A7 (publication_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE publication_access ADD CONSTRAINT FK_E2823A19A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE publication_access ADD CONSTRAINT FK_E2823A1938B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_access DROP FOREIGN KEY FK_E2823A19A76ED395');
        $this->addSql('ALTER TABLE publication_access DROP FOREIGN KEY FK_E2823A1938B217A7');
        $this->addSql('DROP TABLE publication_access');
    }
}
