<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191114114413 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE access_token DROP INDEX UNIQ_B6A2DD686524603F, ADD INDEX IDX_B6A2DD686524603F (auth_token_id)');
        $this->addSql('ALTER TABLE auth_token DROP FOREIGN KEY FK_9315F04E2CCB2688');
        $this->addSql('DROP INDEX UNIQ_9315F04E2CCB2688 ON auth_token');
        $this->addSql('ALTER TABLE auth_token DROP access_token_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE access_token DROP INDEX IDX_B6A2DD686524603F, ADD UNIQUE INDEX UNIQ_B6A2DD686524603F (auth_token_id)');
        $this->addSql('ALTER TABLE auth_token ADD access_token_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE auth_token ADD CONSTRAINT FK_9315F04E2CCB2688 FOREIGN KEY (access_token_id) REFERENCES access_token (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9315F04E2CCB2688 ON auth_token (access_token_id)');
    }
}
