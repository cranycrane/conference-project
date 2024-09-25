<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190407153346 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `user` (
            id INT NOT NULL AUTO_INCREMENT, 
            name VARCHAR(255) NOT NULL, 
            surname VARCHAR(255) NOT NULL, 
            email VARCHAR(255) NOT NULL, 
            username VARCHAR(255) NOT NULL, 
            state INT NOT NULL, 
            password VARCHAR(255) NOT NULL, 
            role VARCHAR(255) NOT NULL, 
            last_logged_at TIMESTAMP DEFAULT NULL, 
            created_at TIMESTAMP NOT NULL, 
            updated_at TIMESTAMP DEFAULT NULL, 
            PRIMARY KEY(id)
        )');
        
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON `user` (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON `user` (username)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE `user`');
    }
}
