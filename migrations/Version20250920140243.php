<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250920140243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registration CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE event_id event_id INT DEFAULT 1 NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE registration ADD CONSTRAINT FK_62A8A7A771F7E88B FOREIGN KEY (event_id) REFERENCES run_event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE registration ADD CONSTRAINT FK_62A8A7A7523FB688 FOREIGN KEY (spectator_id) REFERENCES spectator (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE run_event CHANGE id id INT DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registration MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE registration DROP FOREIGN KEY FK_62A8A7A771F7E88B');
        $this->addSql('ALTER TABLE registration DROP FOREIGN KEY FK_62A8A7A7523FB688');
        $this->addSql('DROP INDEX `primary` ON registration');
        $this->addSql('ALTER TABLE registration CHANGE id id INT NOT NULL, CHANGE event_id event_id INT NOT NULL');
        $this->addSql('ALTER TABLE run_event CHANGE id id INT NOT NULL');
    }
}
