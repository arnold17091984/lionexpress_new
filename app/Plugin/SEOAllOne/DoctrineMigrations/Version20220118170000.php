<?php

namespace Plugin\SEOAllOne\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Eccube\Application;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220118170000 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $stmt = $this->connection->executeQuery("SHOW COLUMNS FROM `plg_seoallone_config` LIKE 'shop_name_top_flg'");
        $cnt = $stmt->fetchColumn();
        if (!$cnt) {
            $this->addSql("ALTER TABLE `plg_seoallone_config` ADD COLUMN `shop_name_top_flg` INT(1) DEFAULT '1' AFTER `line_flg`");
        }
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
