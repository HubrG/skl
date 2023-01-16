<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230112205415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE publication_keyword (id INT AUTO_INCREMENT NOT NULL, keyword VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE publication_keyword_publication (publication_keyword_id INT NOT NULL, publication_id INT NOT NULL, INDEX IDX_3177EB127F2A00B3 (publication_keyword_id), INDEX IDX_3177EB1238B217A7 (publication_id), PRIMARY KEY(publication_keyword_id, publication_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE publication_keyword_publication ADD CONSTRAINT FK_3177EB127F2A00B3 FOREIGN KEY (publication_keyword_id) REFERENCES publication_keyword (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE publication_keyword_publication ADD CONSTRAINT FK_3177EB1238B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publication_keyword_publication DROP FOREIGN KEY FK_3177EB127F2A00B3');
        $this->addSql('ALTER TABLE publication_keyword_publication DROP FOREIGN KEY FK_3177EB1238B217A7');
        $this->addSql('DROP TABLE publication_keyword');
        $this->addSql('DROP TABLE publication_keyword_publication');
    }
}
