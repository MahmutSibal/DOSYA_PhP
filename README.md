# DosyaPHP - Dosya Yönetim Sistemi

Bu proje, kullanıcıların dosya yükleyip yönetebileceği basit bir PHP tabanlı dosya yönetim sistemidir.

## Gereksinimler

- PHP 7.4 veya üzeri
- MySQL/MariaDB veritabanı
- Bir web sunucusu (Apache, Nginx vb.)

## Kurulum

1. **Projeyi İndir**
   - Tüm dosyaları web sunucunuzun kök dizinine kopyalayın.

2. **Veritabanı Oluşturma**
   - MySQL'de `dosya_yonetim` adında bir veritabanı oluşturun.
   - Aşağıdaki SQL sorgularını çalıştırarak tabloları oluşturun:

   ```sql
   CREATE TABLE users (
     id INT AUTO_INCREMENT PRIMARY KEY,
     username VARCHAR(100) NOT NULL,
     email VARCHAR(255) NOT NULL UNIQUE,
     password VARCHAR(255) NOT NULL
   );

   CREATE TABLE files (
     id INT AUTO_INCREMENT PRIMARY KEY,
     user_id INT NOT NULL,
     filename VARCHAR(255) NOT NULL,
     original_name VARCHAR(255) NOT NULL,
     file_type VARCHAR(100) NOT NULL,
     file_size INT NOT NULL,
     uploaded_at DATETIME NOT NULL,
     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
   );
   ```

3. **Veritabanı Bağlantısı Ayarları**
   - `includes/db.php` dosyasındaki kullanıcı adı, şifre ve veritabanı adını kendi bilgilerinizle güncelleyin:

   ```php
   $host = 'localhost';
   $db   = 'dosya_yonetim';
   $user = 'KENDI_KULLANICI_ADINIZ';
   $pass = 'KENDI_SIFRENIZ';
   ```

4. **Uploads Klasörü**
   - Proje dizininde `uploads` adında bir klasör oluşturun ve yazma izni verin.

## Çalıştırma

1. Web tarayıcınızda projeyi açın (ör: `http://localhost/DosyaPhp/index.php`).
2. Kayıt olarak giriş yapın.
3. Dosya yükleyebilir, silebilir ve yönetebilirsiniz.

## Özellikler

- Kullanıcı kaydı ve girişi
- Şifre sıfırlama
- Dosya yükleme (PDF, PNG, JPG)
- Dosya listeleme ve silme

## Güvenlik Notları

- Parolalar güvenli şekilde hashlenir.
- Dosya türü ve boyutu kontrol edilir.
- SQL injection'a karşı hazırlıklı sorgular kullanılır.

---

Herhangi bir sorunla karşılaşırsanız lütfen iletişime geçin.