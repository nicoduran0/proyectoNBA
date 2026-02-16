<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260216185356 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE element_category (element_id INT NOT NULL, category_id INT NOT NULL, PRIMARY KEY (element_id, category_id))');
        $this->addSql('CREATE INDEX IDX_6ABD676C1F1F2A24 ON element_category (element_id)');
        $this->addSql('CREATE INDEX IDX_6ABD676C12469DE2 ON element_category (category_id)');
        $this->addSql('ALTER TABLE element_category ADD CONSTRAINT FK_6ABD676C1F1F2A24 FOREIGN KEY (element_id) REFERENCES element (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE element_category ADD CONSTRAINT FK_6ABD676C12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE element DROP CONSTRAINT fk_41405e3912469de2');
        $this->addSql('DROP INDEX idx_41405e3912469de2');
        $this->addSql('ALTER TABLE element DROP category_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE element_category DROP CONSTRAINT FK_6ABD676C1F1F2A24');
        $this->addSql('ALTER TABLE element_category DROP CONSTRAINT FK_6ABD676C12469DE2');
        $this->addSql('DROP TABLE element_category');
        $this->addSql('ALTER TABLE element ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE element ADD CONSTRAINT fk_41405e3912469de2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_41405e3912469de2 ON element (category_id)');
    }
}
