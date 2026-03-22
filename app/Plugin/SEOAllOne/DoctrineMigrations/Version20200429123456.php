<?php

namespace Plugin\SEOAllOne\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Eccube\Application;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200429123456 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // $this->addSql('CREATE TABLE `plg_seoallone_default` (
        //     `id` INT(10) NOT NULL AUTO_INCREMENT,
        //     `page_id` INT(10) UNSIGNED NOT NULL,
        //     `title` TEXT NULL,
        //     `description` TEXT NULL,
        //     `keyword` TEXT NULL,
        //     `del_flg` TINYINT(1) NOT NULL,
        //     `create_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        //     `update_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        //     PRIMARY KEY (`id`),
        //     INDEX `FK_plg_seoallone_default_dtb_page` (`page_id`),
        //     CONSTRAINT `FK_plg_seoallone_default_dtb_page` FOREIGN KEY (`page_id`) REFERENCES `dtb_page` (`id`)
        // )
        // COLLATE=utf8_general_ci
        // ENGINE=InnoDB
        // ;');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
