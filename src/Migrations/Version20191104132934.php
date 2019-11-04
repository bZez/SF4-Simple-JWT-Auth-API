<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191104132934 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE auth_token CHANGE access_token_id access_token_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE auth_token ADD CONSTRAINT FK_9315F04E2CCB2688 FOREIGN KEY (access_token_id) REFERENCES access_token (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9315F04E2CCB2688 ON auth_token (access_token_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE auth_token DROP FOREIGN KEY FK_9315F04E2CCB2688');
        $this->addSql('DROP INDEX UNIQ_9315F04E2CCB2688 ON auth_token');
        $this->addSql('ALTER TABLE auth_token CHANGE access_token_id access_token_id INT NOT NULL');
    }
}
