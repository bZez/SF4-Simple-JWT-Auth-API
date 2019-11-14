<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191114144355 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE auth_token_access_token (auth_token_id INT NOT NULL, access_token_id INT NOT NULL, INDEX IDX_1A2297AD6524603F (auth_token_id), INDEX IDX_1A2297AD2CCB2688 (access_token_id), PRIMARY KEY(auth_token_id, access_token_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE auth_token_access_token ADD CONSTRAINT FK_1A2297AD6524603F FOREIGN KEY (auth_token_id) REFERENCES auth_token (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE auth_token_access_token ADD CONSTRAINT FK_1A2297AD2CCB2688 FOREIGN KEY (access_token_id) REFERENCES access_token (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE access_token DROP FOREIGN KEY FK_B6A2DD686524603F');
        $this->addSql('DROP INDEX IDX_B6A2DD686524603F ON access_token');
        $this->addSql('ALTER TABLE access_token DROP auth_token_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE auth_token_access_token');
        $this->addSql('ALTER TABLE access_token ADD auth_token_id INT NOT NULL');
        $this->addSql('ALTER TABLE access_token ADD CONSTRAINT FK_B6A2DD686524603F FOREIGN KEY (auth_token_id) REFERENCES auth_token (id)');
        $this->addSql('CREATE INDEX IDX_B6A2DD686524603F ON access_token (auth_token_id)');
    }
}
