<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250216131638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_validated_lessons (user_id INT NOT NULL, lesson_id INT NOT NULL, INDEX IDX_6B363B1AA76ED395 (user_id), INDEX IDX_6B363B1ACDF80196 (lesson_id), PRIMARY KEY(user_id, lesson_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_validated_lessons ADD CONSTRAINT FK_6B363B1AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_validated_lessons ADD CONSTRAINT FK_6B363B1ACDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_validated_lessons DROP FOREIGN KEY FK_6B363B1AA76ED395');
        $this->addSql('ALTER TABLE user_validated_lessons DROP FOREIGN KEY FK_6B363B1ACDF80196');
        $this->addSql('DROP TABLE user_validated_lessons');
    }
}
