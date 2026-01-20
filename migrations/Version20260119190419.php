<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260119190419 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE element ADD api_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE element ADD name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE element ADD team VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE element ADD position VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE element ADD image VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE element ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE element ADD CONSTRAINT FK_41405E3912469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('CREATE INDEX IDX_41405E3912469DE2 ON element (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE element DROP CONSTRAINT FK_41405E3912469DE2');
        $this->addSql('DROP INDEX IDX_41405E3912469DE2');
        $this->addSql('ALTER TABLE element DROP api_id');
        $this->addSql('ALTER TABLE element DROP name');
        $this->addSql('ALTER TABLE element DROP team');
        $this->addSql('ALTER TABLE element DROP position');
        $this->addSql('ALTER TABLE element DROP image');
        $this->addSql('ALTER TABLE element DROP category_id');
    }
}
