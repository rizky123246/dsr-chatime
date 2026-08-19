# DSR Chatime - Daily Sales Report

Aplikasi laporan penjualan harian berbasis Laravel, menggunakan Docker untuk kemudahan instalasi.

## Requirement

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (sudah termasuk Docker Compose)
- Git

## Cara Menjalankan

1. Clone repository ini:
   ```bash
   git clone https://github.com/rizky123246/dsr-chatime.git
   cd dsr-chatime
   ```

2. Copy file environment:
   ```bash
   cp .env.example .env
   ```

3. Build dan jalankan container:
   ```bash
   docker compose up -d --build
   ```
   Proses build pertama kali bisa memakan waktu beberapa menit karena mengunduh image PHP, MySQL, dan menginstall dependency Composer.

4. Generate application key:
   ```bash
   docker compose exec app php artisan key:generate
   ```

5. Jalankan migration dan seeder database:
   ```bash
   docker compose exec app php artisan migrate --seed
   ```

6. Buka aplikasi di browser:
   ```
   http://localhost:8000
   ```

## Akun Login Default (dari seeder)

Semua akun menggunakan password: `password`

| Role | Email |
|---|---|
| Store Manager | john.sm@store.com |
| Area Manager | (lihat `database/seeders/AreaManagerSeeder.php`) |
| Kasir | anna.kasir@store.com |

## Tools Tambahan

- **phpMyAdmin**: http://localhost:8080
  - Server: `db`
  - User: `root`
  - Password: `rootpassword`

## Troubleshooting

**Error permission pada folder `storage`:**
```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
```

**Reset database dari awal (hapus semua data & migrate ulang):**
```bash
docker compose exec app php artisan migrate:fresh --seed
```

**Cek status container:**
```bash
docker compose ps
```

**Cek log aplikasi jika ada error:**
```bash
docker compose logs app --tail=50
```

## Menghentikan Container

```bash
docker compose down
```

Untuk menghentikan sekaligus menghapus data database (hati-hati, data akan hilang):
```bash
docker compose down -v
```
