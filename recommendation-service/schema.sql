CREATE TABLE `data_coaching_fullstate` ( 
  `id` INT AUTO_INCREMENT NOT NULL,
  `judul` VARCHAR(255) NOT NULL,
  `deskripsi` TEXT NULL,
  `kategori` VARCHAR(100) NULL,
  `skor_poin` INT NULL DEFAULT 0 ,
  `target_state` VARCHAR(100) NULL,
  `kode_action` INT NULL DEFAULT 106 ,
  `state` VARCHAR(100) NULL,
  `coaching_type` VARCHAR(100) NULL,
  `session_duration` INT NULL DEFAULT 60 ,
  `total_sessions` INT NULL DEFAULT 1 ,
  `coach_level` VARCHAR(50) NULL,
  `delivery_method` VARCHAR(100) NULL,
  `focus_area` VARCHAR(100) NULL,
  `success_metrics` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `is_active` TINYINT NULL DEFAULT 1 ,
   PRIMARY KEY (`id`)
);

CREATE TABLE `data_hukuman_fullstate` ( 
  `id` INT AUTO_INCREMENT NOT NULL,
  `judul` VARCHAR(255) NOT NULL,
  `deskripsi` TEXT NULL,
  `kategori` VARCHAR(100) NULL,
  `skor_poin` INT NULL DEFAULT 0 ,
  `target_state` VARCHAR(100) NULL,
  `kode_action` INT NULL DEFAULT 103 ,
  `state` VARCHAR(100) NULL,
  `punishment_type` VARCHAR(100) NULL,
  `severity_level` INT NULL DEFAULT 1 ,
  `duration_minutes` INT NULL DEFAULT 30 ,
  `restriction_scope` VARCHAR(100) NULL,
  `appeal_allowed` TINYINT NULL DEFAULT 1 ,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `is_active` TINYINT NULL DEFAULT 1 ,
   PRIMARY KEY (`id`)
);
    
CREATE TABLE `data_misi_fullstate` ( 
  `id` INT AUTO_INCREMENT NOT NULL,
  `judul` VARCHAR(255) NOT NULL,
  `deskripsi` TEXT NULL,
  `kategori` VARCHAR(100) NULL,
  `skor_poin` INT NULL DEFAULT 0 ,
  `target_state` VARCHAR(100) NULL,
  `kode_action` INT NULL,
  `state` VARCHAR(100) NULL,
  `coaching_type` VARCHAR(100) NULL,
  `duration_minutes` INT NULL DEFAULT 30 ,
  `difficulty_level` INT NULL DEFAULT 1 ,
  `prerequisite_missions` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `is_active` TINYINT NULL DEFAULT 1 ,
   PRIMARY KEY (`id`)
);
    
CREATE TABLE `data_produk_fullstate` ( 
  `id` INT AUTO_INCREMENT NOT NULL,
  `judul` VARCHAR(255) NOT NULL,
  `deskripsi` TEXT NULL,
  `kategori` VARCHAR(100) NULL,
  `skor_poin` INT NULL DEFAULT 0 ,
  `target_state` VARCHAR(100) NULL,
  `kode_action` INT NULL DEFAULT 102 ,
  `state` VARCHAR(100) NULL,
  `product_type` VARCHAR(100) NULL,
  `price` DECIMAL(10,2) NULL DEFAULT 0.00 ,
  `access_duration` INT NULL DEFAULT 365 ,
  `difficulty_level` INT NULL DEFAULT 1 ,
  `learning_path` VARCHAR(100) NULL,
  `vendor` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `is_active` TINYINT NULL DEFAULT 1 ,
   PRIMARY KEY (`id`)
);
    
CREATE TABLE `data_reward_fullstate` ( 
  `id` INT AUTO_INCREMENT NOT NULL,
  `judul` VARCHAR(255) NOT NULL,
  `deskripsi` TEXT NULL,
  `kategori` VARCHAR(100) NULL,
  `skor_poin` INT NULL DEFAULT 0 ,
  `target_state` VARCHAR(100) NULL,
  `kode_action` INT NULL DEFAULT 101 ,
  `state` VARCHAR(100) NULL,
  `reward_type` VARCHAR(100) NULL,
  `value_amount` DECIMAL(10,2) NULL DEFAULT 0.00 ,
  `availability_duration` INT NULL DEFAULT 30 ,
  `minimum_score` INT NULL DEFAULT 0 ,
  `prerequisite_achievements` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `is_active` TINYINT NULL DEFAULT 1 ,
   PRIMARY KEY (`id`)
);
    
CREATE TABLE `log_interaksi` ( 
  `id` INT AUTO_INCREMENT NOT NULL,
  `siswa_id` VARCHAR(50) NOT NULL,
  `state` VARCHAR(200) NULL,
  `action` INT NOT NULL,
  `reward` DECIMAL(10,4) NOT NULL,
  `hasil` VARCHAR(100) NULL,
  `interaction_frequency` DECIMAL(5,2) NULL DEFAULT 3.00 ,
  `time_spent` DECIMAL(8,2) NULL DEFAULT 30.00 ,
  `completion_rate` DECIMAL(5,4) NULL DEFAULT 0.7000 ,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
   PRIMARY KEY (`id`)
);
    
CREATE TABLE `q_table_results` ( 
  `id` INT AUTO_INCREMENT NOT NULL,
  `siswa_id` VARCHAR(50) NOT NULL,
  `state` VARCHAR(200) NOT NULL,
  `action_101` DECIMAL(15,8) NULL DEFAULT 0.00000000 ,
  `action_102` DECIMAL(15,8) NULL DEFAULT 0.00000000 ,
  `action_103` DECIMAL(15,8) NULL DEFAULT 0.00000000 ,
  `action_105` DECIMAL(15,8) NULL DEFAULT 0.00000000 ,
  `action_106` DECIMAL(15,8) NULL DEFAULT 0.00000000 ,
  `best_action` INT NOT NULL,
  `max_q_value` DECIMAL(15,8) NOT NULL,
  `rekomendasi` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
   PRIMARY KEY (`id`),
  CONSTRAINT `unique_siswa_state` UNIQUE (`siswa_id`, `state`)
);
    
CREATE TABLE `q_table_results_advanced` ( 
  `id` INT AUTO_INCREMENT NOT NULL,
  `siswa_id` VARCHAR(50) NOT NULL,
  `state` VARCHAR(200) NOT NULL,
  `action_101` DECIMAL(15,8) NULL DEFAULT 0.00000000 ,
  `action_102` DECIMAL(15,8) NULL DEFAULT 0.00000000 ,
  `action_103` DECIMAL(15,8) NULL DEFAULT 0.00000000 ,
  `action_105` DECIMAL(15,8) NULL DEFAULT 0.00000000 ,
  `action_106` DECIMAL(15,8) NULL DEFAULT 0.00000000 ,
  `best_action` INT NOT NULL,
  `max_q_value` DECIMAL(15,8) NOT NULL,
  `rekomendasi` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
   PRIMARY KEY (`id`),
  CONSTRAINT `unique_siswa_state` UNIQUE (`siswa_id`, `state`)
);
    
CREATE TABLE `q_table_results_quick` ( 
  `id` INT AUTO_INCREMENT NOT NULL,
  `siswa_id` VARCHAR(50) NOT NULL,
  `state` VARCHAR(200) NOT NULL,
  `action_101` DECIMAL(15,8) NULL DEFAULT 0.00000000 ,
  `action_102` DECIMAL(15,8) NULL DEFAULT 0.00000000 ,
  `action_103` DECIMAL(15,8) NULL DEFAULT 0.00000000 ,
  `action_105` DECIMAL(15,8) NULL DEFAULT 0.00000000 ,
  `action_106` DECIMAL(15,8) NULL DEFAULT 0.00000000 ,
  `best_action` INT NOT NULL,
  `max_q_value` DECIMAL(15,8) NOT NULL,
  `rekomendasi` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
   PRIMARY KEY (`id`),
  CONSTRAINT `unique_siswa_state` UNIQUE (`siswa_id`, `state`)
);
    
CREATE TABLE `scores_siswa` ( 
  `id` INT AUTO_INCREMENT NOT NULL,
  `siswa_id` VARCHAR(50) NOT NULL,
  `vark` DECIMAL(10,4) NULL,
  `mslq` DECIMAL(10,4) NULL,
  `ams` DECIMAL(10,4) NULL,
  `engagement` ENUM('basic','medium','high') NULL DEFAULT 'medium' ,
   PRIMARY KEY (`id`),
  CONSTRAINT `siswa_id` UNIQUE (`siswa_id`)
);
    
CREATE TABLE `unique_states` ( 
  `id` INT AUTO_INCREMENT NOT NULL,
  `state` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
   PRIMARY KEY (`id`),
  CONSTRAINT `state` UNIQUE (`state`);
);

-- Unified catalog table for recommendation items (refs-only)
CREATE TABLE IF NOT EXISTS `items` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `state` VARCHAR(200) NOT NULL,
  `action_code` INT NOT NULL,
  `ref_type` ENUM('mission','product','reward','coaching','punishment','badge') NOT NULL,
  `ref_id` BIGINT NOT NULL,
  `is_active` TINYINT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_items_state_action_ref` (`state`,`action_code`,`ref_type`,`ref_id`),
  KEY `idx_items_state_action` (`state`,`action_code`),
  KEY `idx_items_ref` (`ref_type`,`ref_id`)
);
