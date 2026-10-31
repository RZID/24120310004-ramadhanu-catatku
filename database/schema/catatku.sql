-- Catatku - struktur tabel + data demo (MySQL 8.4).
--
-- Dibuat dengan:
--   mysqldump --no-tablespaces --skip-comments --skip-add-locks \
--     catatku migrations users wallets categories transactions
--
-- Impor ke database kosong (nama bebas, tidak ada USE/CREATE DATABASE di file ini):
--   mysql -u root -p -e "CREATE DATABASE catatku_impor"
--   mysql -u root -p catatku_impor < database/schema/catatku.sql
--
-- Akun demo: admin@catatku.test, budi@catatku.test, siti@catatku.test - kata sandi "password".


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_sessions_table',1),(4,'2026_10_31_000001_create_wallets_table',1),(5,'2026_10_31_000002_create_categories_table',1),(6,'2026_10_31_000003_create_transactions_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `deactivated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin Catatku','admin@catatku.test','$2y$12$T9YE8gkw40W/16dtLP584uM9V2B9FUZWFS0wB/E7S.gqDocfV.utG','admin',NULL,'2026-10-31 16:23:55','2026-10-31 16:23:55'),(2,'Budi Santoso','budi@catatku.test','$2y$12$H96R0ydZuV/NDAHlYMtU/eskLGGxQkwsANPcNOIsp9O9ij8gvYqT.','user',NULL,'2026-10-31 16:23:55','2026-10-31 16:23:55'),(3,'Siti Rahayu','siti@catatku.test','$2y$12$ORtnbw30yn13M5RtcOkXV.nsehZp1dpIEJQsfpbI2UPgUaGIqKhLO','user',NULL,'2026-10-31 16:23:56','2026-10-31 16:23:56');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
DROP TABLE IF EXISTS `wallets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wallets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallets_user_id_name_unique` (`user_id`,`name`),
  CONSTRAINT `wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `wallets` DISABLE KEYS */;
INSERT INTO `wallets` VALUES (1,2,'Dompet Tunai','2026-10-31 16:23:55','2026-10-31 16:23:55'),(2,2,'BCA','2026-10-31 16:23:55','2026-10-31 16:23:55'),(3,2,'GoPay','2026-10-31 16:23:55','2026-10-31 16:23:55'),(4,3,'Dompet Tunai','2026-10-31 16:23:56','2026-10-31 16:23:56'),(5,3,'Mandiri','2026-10-31 16:23:56','2026-10-31 16:23:56');
/*!40000 ALTER TABLE `wallets` ENABLE KEYS */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_user_id_name_type_unique` (`user_id`,`name`,`type`),
  CONSTRAINT `categories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,2,'Gaji','income','2026-10-31 16:23:55','2026-10-31 16:23:55'),(2,2,'Bonus','income','2026-10-31 16:23:55','2026-10-31 16:23:55'),(3,2,'Freelance','income','2026-10-31 16:23:55','2026-10-31 16:23:55'),(4,2,'Makan','expense','2026-10-31 16:23:55','2026-10-31 16:23:55'),(5,2,'Transportasi','expense','2026-10-31 16:23:55','2026-10-31 16:23:55'),(6,2,'Belanja','expense','2026-10-31 16:23:55','2026-10-31 16:23:55'),(7,2,'Tagihan','expense','2026-10-31 16:23:55','2026-10-31 16:23:55'),(8,2,'Hiburan','expense','2026-10-31 16:23:55','2026-10-31 16:23:55'),(9,2,'Kesehatan','expense','2026-10-31 16:23:55','2026-10-31 16:23:55'),(10,3,'Gaji','income','2026-10-31 16:23:56','2026-10-31 16:23:56'),(11,3,'Usaha','income','2026-10-31 16:23:56','2026-10-31 16:23:56'),(12,3,'Makan','expense','2026-10-31 16:23:56','2026-10-31 16:23:56'),(13,3,'Transportasi','expense','2026-10-31 16:23:56','2026-10-31 16:23:56'),(14,3,'Belanja','expense','2026-10-31 16:23:56','2026-10-31 16:23:56'),(15,3,'Tagihan','expense','2026-10-31 16:23:56','2026-10-31 16:23:56'),(16,3,'Pendidikan','expense','2026-10-31 16:23:56','2026-10-31 16:23:56');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `wallet_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` bigint unsigned NOT NULL,
  `transacted_on` date NOT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_wallet_id_foreign` (`wallet_id`),
  KEY `transactions_category_id_foreign` (`category_id`),
  KEY `transactions_user_id_transacted_on_index` (`user_id`,`transacted_on`),
  CONSTRAINT `transactions_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,2,1,4,'expense',45000,'2026-08-01','Sarapan bubur ayam','2026-10-31 16:23:55','2026-10-31 16:23:55'),(2,2,3,5,'expense',25000,'2026-08-03','Ojek ke kantor','2026-10-31 16:23:55','2026-10-31 16:23:55'),(3,2,2,6,'expense',320000,'2026-08-05','Belanja bulanan Indomaret','2026-10-31 16:23:55','2026-10-31 16:23:55'),(4,2,3,4,'expense',38000,'2026-08-08','Makan siang warteg','2026-10-31 16:23:55','2026-10-31 16:23:55'),(5,2,2,7,'expense',450000,'2026-08-10','Listrik dan air','2026-10-31 16:23:55','2026-10-31 16:23:55'),(6,2,3,8,'expense',60000,'2026-08-12','Nonton bioskop','2026-10-31 16:23:55','2026-10-31 16:23:55'),(7,2,3,5,'expense',30000,'2026-08-15','Bensin motor','2026-10-31 16:23:55','2026-10-31 16:23:55'),(8,2,1,4,'expense',52000,'2026-08-20','Makan malam keluarga','2026-10-31 16:23:55','2026-10-31 16:23:55'),(9,2,2,1,'income',8500000,'2026-08-25','Gaji bulan Agustus','2026-10-31 16:23:55','2026-10-31 16:23:55'),(10,2,1,9,'expense',150000,'2026-08-28','Beli vitamin','2026-10-31 16:23:55','2026-10-31 16:23:55'),(11,2,1,4,'expense',40000,'2026-09-02','Sarapan nasi uduk','2026-10-31 16:23:55','2026-10-31 16:23:55'),(12,2,2,6,'expense',275000,'2026-09-04','Belanja sayur dan buah','2026-10-31 16:23:55','2026-10-31 16:23:55'),(13,2,3,5,'expense',22000,'2026-09-07','Ojek ke stasiun','2026-10-31 16:23:55','2026-10-31 16:23:55'),(14,2,2,7,'expense',465000,'2026-09-09','Listrik dan air','2026-10-31 16:23:55','2026-10-31 16:23:55'),(15,2,3,4,'expense',42000,'2026-09-11','Makan siang padang','2026-10-31 16:23:55','2026-10-31 16:23:55'),(16,2,2,3,'income',1750000,'2026-09-14','Proyek desain logo','2026-10-31 16:23:55','2026-10-31 16:23:55'),(17,2,3,8,'expense',75000,'2026-09-18','Konser musik kampus','2026-10-31 16:23:55','2026-10-31 16:23:55'),(18,2,1,4,'expense',48000,'2026-09-22','Makan malam','2026-10-31 16:23:55','2026-10-31 16:23:55'),(19,2,2,1,'income',8500000,'2026-09-25','Gaji bulan September','2026-10-31 16:23:55','2026-10-31 16:23:55'),(20,2,1,5,'expense',20000,'2026-09-29','Angkot','2026-10-31 16:23:55','2026-10-31 16:23:55'),(21,2,1,4,'expense',35000,'2026-10-01','Sarapan bubur','2026-10-31 16:23:55','2026-10-31 16:23:55'),(22,2,2,6,'expense',310000,'2026-10-03','Belanja bulanan','2026-10-31 16:23:55','2026-10-31 16:23:55'),(23,2,3,5,'expense',27000,'2026-10-06','Ojek ke kantor','2026-10-31 16:23:55','2026-10-31 16:23:55'),(24,2,2,7,'expense',470000,'2026-10-08','Listrik dan air','2026-10-31 16:23:55','2026-10-31 16:23:55'),(25,2,2,2,'income',1000000,'2026-10-10','Bonus proyek','2026-10-31 16:23:55','2026-10-31 16:23:55'),(26,2,3,4,'expense',39000,'2026-10-13','Makan siang','2026-10-31 16:23:55','2026-10-31 16:23:55'),(27,2,1,9,'expense',210000,'2026-10-17','Periksa gigi','2026-10-31 16:23:55','2026-10-31 16:23:55'),(28,2,3,8,'expense',55000,'2026-10-21','Langganan streaming','2026-10-31 16:23:56','2026-10-31 16:23:56'),(29,2,2,1,'income',8500000,'2026-10-25','Gaji bulan Oktober','2026-10-31 16:23:56','2026-10-31 16:23:56'),(30,2,1,4,'expense',44000,'2026-10-29','Makan malam','2026-10-31 16:23:56','2026-10-31 16:23:56'),(31,3,4,12,'expense',30000,'2026-08-02','Sarapan nasi kuning','2026-10-31 16:23:56','2026-10-31 16:23:56'),(32,3,4,13,'expense',18000,'2026-08-05','Angkot ke pasar','2026-10-31 16:23:56','2026-10-31 16:23:56'),(33,3,5,14,'expense',195000,'2026-08-07','Belanja bahan masakan','2026-10-31 16:23:56','2026-10-31 16:23:56'),(34,3,5,15,'expense',320000,'2026-08-11','Listrik rumah','2026-10-31 16:23:56','2026-10-31 16:23:56'),(35,3,5,16,'expense',350000,'2026-08-14','Buku pelajaran anak','2026-10-31 16:23:56','2026-10-31 16:23:56'),(36,3,4,12,'expense',36000,'2026-08-18','Makan siang','2026-10-31 16:23:56','2026-10-31 16:23:56'),(37,3,5,11,'income',950000,'2026-08-22','Penjualan kue kering','2026-10-31 16:23:56','2026-10-31 16:23:56'),(38,3,5,10,'income',6200000,'2026-08-26','Gaji bulan Agustus','2026-10-31 16:23:56','2026-10-31 16:23:56'),(39,3,4,13,'expense',25000,'2026-08-29','Ojek ke sekolah','2026-10-31 16:23:56','2026-10-31 16:23:56'),(40,3,4,12,'expense',41000,'2026-08-31','Makan malam keluarga','2026-10-31 16:23:56','2026-10-31 16:23:56'),(41,3,4,12,'expense',28000,'2026-09-03','Sarapan','2026-10-31 16:23:56','2026-10-31 16:23:56'),(42,3,5,14,'expense',210000,'2026-09-06','Belanja mingguan','2026-10-31 16:23:56','2026-10-31 16:23:56'),(43,3,5,15,'expense',335000,'2026-09-09','Listrik rumah','2026-10-31 16:23:56','2026-10-31 16:23:56'),(44,3,4,13,'expense',20000,'2026-09-12','Angkot','2026-10-31 16:23:56','2026-10-31 16:23:56'),(45,3,5,16,'expense',425000,'2026-09-15','Les matematika','2026-10-31 16:23:56','2026-10-31 16:23:56'),(46,3,4,12,'expense',33000,'2026-09-19','Makan siang','2026-10-31 16:23:56','2026-10-31 16:23:56'),(47,3,5,11,'income',1120000,'2026-09-23','Pesanan katering','2026-10-31 16:23:56','2026-10-31 16:23:56'),(48,3,5,10,'income',6200000,'2026-09-26','Gaji bulan September','2026-10-31 16:23:56','2026-10-31 16:23:56'),(49,3,5,14,'expense',165000,'2026-09-28','Belanja sayur','2026-10-31 16:23:56','2026-10-31 16:23:56'),(50,3,4,12,'expense',39000,'2026-09-30','Makan malam','2026-10-31 16:23:56','2026-10-31 16:23:56'),(51,3,4,12,'expense',32000,'2026-10-02','Sarapan','2026-10-31 16:23:56','2026-10-31 16:23:56'),(52,3,5,14,'expense',240000,'2026-10-05','Belanja bulanan','2026-10-31 16:23:56','2026-10-31 16:23:56'),(53,3,4,13,'expense',22000,'2026-10-07','Ojek ke pasar','2026-10-31 16:23:56','2026-10-31 16:23:56'),(54,3,5,15,'expense',340000,'2026-10-11','Listrik rumah','2026-10-31 16:23:56','2026-10-31 16:23:56'),(55,3,5,16,'expense',300000,'2026-10-14','Buku dan alat tulis','2026-10-31 16:23:56','2026-10-31 16:23:56'),(56,3,5,11,'income',780000,'2026-10-18','Penjualan kue','2026-10-31 16:23:56','2026-10-31 16:23:56'),(57,3,4,12,'expense',37000,'2026-10-20','Makan siang','2026-10-31 16:23:56','2026-10-31 16:23:56'),(58,3,5,10,'income',6200000,'2026-10-24','Gaji bulan Oktober','2026-10-31 16:23:56','2026-10-31 16:23:56'),(59,3,5,14,'expense',185000,'2026-10-27','Belanja bahan masakan','2026-10-31 16:23:56','2026-10-31 16:23:56'),(60,3,4,12,'expense',40000,'2026-10-30','Makan malam','2026-10-31 16:23:56','2026-10-31 16:23:56');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

