<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250110193855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE income_request ADD COLUMN error_message CLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE income_request ADD COLUMN output_file VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE income_request ADD COLUMN completed_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL COLLATE "BINARY", headers CLOB NOT NULL COLLATE "BINARY", queue_name VARCHAR(190) NOT NULL COLLATE "BINARY", created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , available_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , delivered_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__income_request AS SELECT id, json_data, template_name, created_at, status FROM income_request');
        $this->addSql('DROP TABLE income_request');
        $this->addSql('CREATE TABLE income_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, json_data CLOB NOT NULL, template_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , status VARCHAR(20) DEFAULT \'new\' NOT NULL)');
        $this->addSql('INSERT INTO income_request (id, json_data, template_name, created_at, status) SELECT id, json_data, template_name, created_at, status FROM __temp__income_request');
        $this->addSql('DROP TABLE __temp__income_request');
    }
}
