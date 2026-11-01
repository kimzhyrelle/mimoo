CREATE DATABASE IF NOT EXISTS mimoo_rider
  CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE mimoo_rider;

CREATE TABLE IF NOT EXISTS rider_profile (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id VARCHAR(64) NOT NULL UNIQUE,
  name VARCHAR(160) NOT NULL,
  phone VARCHAR(32) NOT NULL,
  address VARCHAR(500) NOT NULL,
  vehicle_type VARCHAR(80) NOT NULL,
  plate_no VARCHAR(32) NOT NULL,
  license_no VARCHAR(64) NOT NULL,
  license_expires_on DATE NULL,
  or_cr_expires_on DATE NULL,
  kyc_status VARCHAR(32) NOT NULL DEFAULT 'pending',
  area_id VARCHAR(64) NULL,
  area_name VARCHAR(120) NULL,
  fleet_name VARCHAR(160) NULL,
  hub_name VARCHAR(160) NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  checked_in_at DATETIME(3) NULL,
  suspended_at DATETIME(3) NULL,
  suspension_reason VARCHAR(500) NULL,
  photo_path VARCHAR(500) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rider_accounts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  rider_profile_id BIGINT UNSIGNED NOT NULL UNIQUE,
  email VARCHAR(254) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  CONSTRAINT fk_accounts_profile FOREIGN KEY (rider_profile_id)
    REFERENCES rider_profile(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS parcels (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  rider_profile_id BIGINT UNSIGNED NULL,
  tracking_no VARCHAR(80) NOT NULL UNIQUE,
  job_type ENUM('pickup','delivery') NOT NULL,
  status ENUM('ready_for_pickup','assigned','picked_up','at_hub',
    'out_for_delivery','delivered','failed_delivery','return_to_sender',
    'returned') NOT NULL,
  seller_name VARCHAR(180) NOT NULL DEFAULT '',
  seller_address VARCHAR(500) NOT NULL DEFAULT '',
  buyer_name VARCHAR(180) NOT NULL DEFAULT '',
  buyer_phone VARCHAR(32) NOT NULL DEFAULT '',
  address VARCHAR(500) NOT NULL DEFAULT '',
  area_id VARCHAR(64) NULL,
  area_name VARCHAR(120) NULL,
  cod_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  is_prepaid BOOLEAN NOT NULL DEFAULT FALSE,
  cod_collected BOOLEAN NOT NULL DEFAULT FALSE,
  cod_remitted BOOLEAN NOT NULL DEFAULT FALSE,
  delivery_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  failure_reason VARCHAR(120) NULL,
  pickup_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
  delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
  delivery_otp_verified BOOLEAN NOT NULL DEFAULT FALSE,
  pod_photo_path VARCHAR(500) NULL,
  assigned_at DATETIME(3) NULL,
  picked_up_at DATETIME(3) NULL,
  out_for_delivery_at DATETIME(3) NULL,
  delivered_at DATETIME(3) NULL,
  sla_due_at DATETIME(3) NULL,
  updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  synced BOOLEAN NOT NULL DEFAULT TRUE,
  route_code VARCHAR(32) NULL,
  landmark VARCHAR(255) NULL,
  carrier VARCHAR(80) NULL,
  item VARCHAR(180) NULL,
  weight_kg DECIMAL(8,2) NULL,
  received_at DATETIME(3) NULL,
  sorted_at DATETIME(3) NULL,
  INDEX idx_parcels_rider_status (rider_profile_id, status),
  CONSTRAINT fk_parcels_rider FOREIGN KEY (rider_profile_id)
    REFERENCES rider_profile(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS parcel_status_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  parcel_id BIGINT UNSIGNED NOT NULL,
  from_status VARCHAR(32) NULL,
  to_status VARCHAR(32) NOT NULL,
  reason VARCHAR(120) NULL,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  INDEX idx_logs_parcel_created (parcel_id, created_at),
  CONSTRAINT fk_logs_parcel FOREIGN KEY (parcel_id)
    REFERENCES parcels(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS job_offers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  rider_profile_id BIGINT UNSIGNED NOT NULL,
  parcel_id VARCHAR(80) NOT NULL,
  area_name VARCHAR(120) NOT NULL,
  payout DECIMAL(10,2) NOT NULL,
  cod_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  km DECIMAL(8,2) NOT NULL DEFAULT 0,
  expires_at DATETIME(3) NOT NULL,
  state ENUM('pending','accepted','declined','expired') NOT NULL DEFAULT 'pending',
  CONSTRAINT fk_offers_rider FOREIGN KEY (rider_profile_id)
    REFERENCES rider_profile(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cod_remittances (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  rider_profile_id BIGINT UNSIGNED NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  collected DECIMAL(12,2) NOT NULL,
  difference DECIMAL(12,2) NOT NULL,
  status ENUM('pending','confirmed') NOT NULL DEFAULT 'pending',
  reference VARCHAR(120) NOT NULL,
  method VARCHAR(40) NOT NULL,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  confirmed_at DATETIME(3) NULL,
  CONSTRAINT fk_remittances_rider FOREIGN KEY (rider_profile_id)
    REFERENCES rider_profile(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  rider_profile_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  body TEXT NOT NULL,
  kind VARCHAR(40) NOT NULL,
  parcel_id VARCHAR(80) NULL,
  read_at DATETIME(3) NULL,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  CONSTRAINT fk_notifications_rider FOREIGN KEY (rider_profile_id)
    REFERENCES rider_profile(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS conversations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  rider_profile_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  last_message_at DATETIME(3) NULL,
  CONSTRAINT fk_conversations_rider FOREIGN KEY (rider_profile_id)
    REFERENCES rider_profile(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS messages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  conversation_id BIGINT UNSIGNED NOT NULL,
  sender ENUM('rider','hub') NOT NULL,
  body TEXT NOT NULL,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  read_at DATETIME(3) NULL,
  CONSTRAINT fk_messages_conversation FOREIGN KEY (conversation_id)
    REFERENCES conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS emergency_contacts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  rider_profile_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  relationship VARCHAR(80) NOT NULL,
  phone VARCHAR(32) NOT NULL,
  CONSTRAINT fk_contacts_rider FOREIGN KEY (rider_profile_id)
    REFERENCES rider_profile(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS outbox (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  entity VARCHAR(64) NOT NULL,
  entity_id VARCHAR(100) NOT NULL,
  action VARCHAR(64) NOT NULL,
  payload_json JSON NOT NULL,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  attempts INT UNSIGNED NOT NULL DEFAULT 0,
  last_error TEXT NULL
) ENGINE=InnoDB;
