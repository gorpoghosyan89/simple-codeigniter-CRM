<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Agencies_table extends CI_Migration {

        public function up() {

            $this->db->query("CREATE TABLE IF NOT EXISTS `agencies` (`id` int(10) NOT NULL,`name` varchar(256) NOT NULL,`abbreviation` varchar(256) DEFAULT NULL,`url_slug` varchar(256) NOT NULL,`gov_id` int(10) DEFAULT NULL,`parent_gov_id` int(10) DEFAULT NULL);");
            $this->db->query("ALTER TABLE `agencies` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `url_slug` (`url_slug`);");
            $this->db->query("ALTER TABLE `agencies` MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;");
            
        }

}





