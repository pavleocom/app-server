<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230813144216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Password reset table.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE password_reset (id VARCHAR(255) NOT NULL, user_id UUID NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B1017252A76ED395 ON password_reset (user_id)');
        $this->addSql('CREATE INDEX idx_password_reset_created_at ON password_reset (created_at)');
        $this->addSql('CREATE INDEX idx_password_reset_expires_at ON password_reset (expires_at)');
        $this->addSql('COMMENT ON COLUMN password_reset.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN password_reset.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN password_reset.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE password_reset ADD CONSTRAINT FK_B1017252A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE password_reset DROP CONSTRAINT FK_B1017252A76ED395');
        $this->addSql('DROP TABLE password_reset');
    }
}
