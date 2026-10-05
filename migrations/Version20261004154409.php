<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261004154409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_failure (id INT AUTO_INCREMENT NOT NULL, error_type LONGTEXT NOT NULL, payload JSON NOT NULL, created_at DATETIME NOT NULL, is_fix TINYINT DEFAULT NULL, fixed_at DATETIME DEFAULT NULL, fixed_by VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE product CHANGE brand brand VARCHAR(255) DEFAULT NULL, CHANGE usd_price usd_price DOUBLE PRECISION DEFAULT NULL, CHANGE is_available is_available TINYINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE product_failure');
        $this->addSql('ALTER TABLE product CHANGE brand brand VARCHAR(255) NOT NULL, CHANGE usd_price usd_price DOUBLE PRECISION NOT NULL, CHANGE is_available is_available TINYINT NOT NULL');
    }
}
