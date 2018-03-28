<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180326130937 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE contract (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, date_updated DATETIME NOT NULL, INDEX IDX_E98F2859A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, fullname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, birthday DATE NOT NULL, date_updated DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page (id INT AUTO_INCREMENT NOT NULL, contract_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, num INT NOT NULL, date_updated DATETIME NOT NULL, INDEX IDX_140AB6202576E0FD (contract_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F2859A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB6202576E0FD FOREIGN KEY (contract_id) REFERENCES contract (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB6202576E0FD');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F2859A76ED395');
        $this->addSql('DROP TABLE contract');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE page');
    }
}
