<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261004145733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, scrapping_url LONGTEXT DEFAULT NULL, seller VARCHAR(255) NOT NULL, brand VARCHAR(255) NOT NULL, usd_price DOUBLE PRECISION NOT NULL, is_available TINYINT NOT NULL, description LONGTEXT DEFAULT NULL, customer_says LONGTEXT DEFAULT NULL, features JSON DEFAULT NULL, variants JSON DEFAULT NULL, last_scrapping_at DATETIME DEFAULT NULL, details JSON DEFAULT NULL, slug VARCHAR(255) DEFAULT NULL, uid VARCHAR(10) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE product');
    }
}
