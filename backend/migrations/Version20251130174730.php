<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251130174730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE shopping_list (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, owner_id INT NOT NULL, INDEX IDX_3DC1A4597E3C61F9 (owner_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE shopping_list_item (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, quantity VARCHAR(255) DEFAULT NULL, is_completed TINYINT DEFAULT 0 NOT NULL, shopping_list_id INT NOT NULL, INDEX IDX_4FB1C22423245BF9 (shopping_list_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE refresh_tokens (refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, UNIQUE INDEX UNIQ_9BACE7E1C74F2195 (refresh_token), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE shopping_list ADD CONSTRAINT FK_3DC1A4597E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE shopping_list_item ADD CONSTRAINT FK_4FB1C22423245BF9 FOREIGN KEY (shopping_list_id) REFERENCES shopping_list (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shopping_list DROP FOREIGN KEY FK_3DC1A4597E3C61F9');
        $this->addSql('ALTER TABLE shopping_list_item DROP FOREIGN KEY FK_4FB1C22423245BF9');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE shopping_list');
        $this->addSql('DROP TABLE shopping_list_item');
        $this->addSql('DROP TABLE user');
    }
}
