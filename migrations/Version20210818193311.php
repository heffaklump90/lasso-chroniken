<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210818193311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE strava_activity (id INT AUTO_INCREMENT NOT NULL, data LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD strava_athlete_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64915DF9797 FOREIGN KEY (strava_athlete_id) REFERENCES strava_athlete (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64915DF9797 ON user (strava_athlete_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE strava_activity');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D64915DF9797');
        $this->addSql('DROP INDEX UNIQ_8D93D64915DF9797 ON `user`');
        $this->addSql('ALTER TABLE `user` DROP strava_athlete_id');
    }
}
