-- +goose Up
-- +goose StatementBegin
CREATE TABLE IF NOT EXISTS `nlp_lexicon` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `keyword` VARCHAR(255) NOT NULL,
  `style` ENUM('Visual', 'Aural', 'Read/Write', 'Kinesthetic') NOT NULL,
  `weight` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_keyword_style` (`keyword`, `style`)
);
-- +goose StatementEnd

-- +goose Down
-- +goose StatementBegin
DROP TABLE IF EXISTS `nlp_lexicon`;
-- +goose StatementEnd
