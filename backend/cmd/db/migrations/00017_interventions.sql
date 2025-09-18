-- +goose Up
-- +goose StatementBegin
-- Catalog tables
CREATE TABLE coachings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    duration_minutes INT NULL,
    sessions INT NULL,
    modality ENUM('online','offline','hybrid') NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE rewards (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    type VARCHAR(50) NOT NULL,
    metadata JSON NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE punishments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    severity SMALLINT NULL,
    metadata JSON NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Link / audit tables
CREATE TABLE user_coaching (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    coaching_id BIGINT NOT NULL,
    status ENUM('requested','scheduled','completed','canceled') NOT NULL DEFAULT 'requested',
    scheduled_at DATETIME NULL,
    completed_at DATETIME NULL,
    notes TEXT NULL,
    created_by BIGINT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_coaching_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_coaching_coaching FOREIGN KEY (coaching_id) REFERENCES coachings(id) ON DELETE RESTRICT,
    CONSTRAINT fk_user_coaching_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE user_rewards (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    reward_id BIGINT NOT NULL,
    status ENUM('claimed','applied','revoked') NOT NULL DEFAULT 'claimed',
    applied_at DATETIME NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_rewards_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_rewards_reward FOREIGN KEY (reward_id) REFERENCES rewards(id) ON DELETE RESTRICT
);

CREATE TABLE user_punishments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    punishment_id BIGINT NOT NULL,
    status ENUM('assigned','acknowledged','active','completed','lifted','appealed') NOT NULL DEFAULT 'assigned',
    effective_from DATETIME NULL,
    effective_to DATETIME NULL,
    reason TEXT NULL,
    issued_by BIGINT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_punishments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_punishments_punishment FOREIGN KEY (punishment_id) REFERENCES punishments(id) ON DELETE RESTRICT,
    CONSTRAINT fk_user_punishments_issued_by FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL
);
-- +goose StatementEnd

-- +goose Down
-- +goose StatementBegin
DROP TABLE IF EXISTS user_punishments;
DROP TABLE IF EXISTS user_rewards;
DROP TABLE IF EXISTS user_coaching;
DROP TABLE IF EXISTS punishments;
DROP TABLE IF EXISTS rewards;
DROP TABLE IF EXISTS coachings;
-- +goose StatementEnd

