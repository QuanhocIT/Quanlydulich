CREATE TABLE IF NOT EXISTS khach_hang_tour_yeu_thich (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  khach_hang_id INT NOT NULL,
  tour_id INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_kh_tour_yeu_thich (khach_hang_id, tour_id),
  KEY idx_tour_yeu_thich_tour (tour_id),
  CONSTRAINT fk_tour_yeu_thich_khach_hang
    FOREIGN KEY (khach_hang_id) REFERENCES khach_hang(khach_hang_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_tour_yeu_thich_tour
    FOREIGN KEY (tour_id) REFERENCES tour(tour_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
