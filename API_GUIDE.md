# InvoicePlane Read-Only API Documentation

Dokumentasi ini menjelaskan cara menggunakan API Endpoint Read-Only yang diisolasi pada folder `application/modules/api/`.

## Ketentuan Umum

* **Base URL:** `https://your-domain.com/index.php`
* **Format Request/Response:** `application/json`
* **HTTP Method:** Hanya mendukung request **`GET`** (dan preflight **`OPTIONS`**). Request `POST`, `PUT`, atau `DELETE` akan ditolak dengan status code `405 Method Not Allowed`.

---

## Autentikasi

Setiap request wajib menyertakan HTTP Header berikut:

| Header | Nilai | Deskripsi |
| :--- | :--- | :--- |
| `X-API-KEY` | `your_configured_api_key` | API Key rahasia yang telah dikonfigurasi di file `ipconfig.php` |

---

## Daftar Endpoint

### 1. Dasbor Statistik & Keuangan (`/api/stats`)
Mengambil rangkuman statistik keuangan (total invoiced, paid, balance) untuk bulan ini, tahun ini, dan sepanjang waktu, serta status breakdown invoice.

* **URL:** `/api/stats`
* **Contoh Request:**
  ```bash
  curl -i -H "X-API-KEY: your_api_key" "https://your-domain.com/index.php/api/stats"
  ```
* **Contoh Response Sukses:**
  ```json
  {
      "success": true,
      "data": {
          "overview_period": "this-month",
          "amounts": {
              "invoiced": { "month": 2000000, "year": 107358000, "total": 1009334640 },
              "paid": { "month": 0, "year": 101858000, "total": 973834640 },
              "balance": { "month": 2000000, "year": 5500000, "total": 35500000 }
          },
          "invoice_status_breakdown": [
              { "status_id": 1, "label": "Draft", "sum_total": 0, "num_total": 0 },
              { "status_id": 2, "label": "Sent", "sum_total": 0, "num_total": 0 },
              { "status_id": 3, "label": "Viewed", "sum_total": 2000000, "num_total": 1 },
              { "status_id": 4, "label": "Paid", "sum_total": 0, "num_total": 0 }
          ]
      }
  }
  ```

---

### 2. Daftar Klien (`/api/clients`)
Mendapatkan daftar kontak klien beserta total tagihan dan saldo piutang kumulatif mereka.

* **URL:** `/api/clients`
* **Query Parameters:**
  * `limit` (int, default: 50) - Jumlah maksimal data yang dikembalikan.
  * `offset` (int, default: 0) - Indeks awal pemotongan data (untuk pagination).
  * `status` (string, default: `active`) - Opsi: `active` (hanya klien aktif), `inactive` (hanya non-aktif), atau `all` (semua).
* **Contoh Request:**
  ```bash
  curl -i -H "X-API-KEY: your_api_key" "https://your-domain.com/index.php/api/clients?limit=10&status=active"
  ```

---

### 3. Detail Profil Klien (`/api/clients/detail/<id>`)
Mendapatkan profil lengkap satu klien tertentu berdasarkan ID klien.

* **URL:** `/api/clients/detail/<client_id>`
* **Contoh Request:**
  ```bash
  curl -i -H "X-API-KEY: your_api_key" "https://your-domain.com/index.php/api/clients/detail/28"
  ```

---

### 4. Daftar Invoice Spesifik Klien (`/api/clients/invoices/<id>`)
Mendapatkan seluruh daftar invoice yang dialokasikan khusus untuk ID klien tertentu.

* **URL:** `/api/clients/invoices/<client_id>`
* **Contoh Request:**
  ```bash
  curl -i -H "X-API-KEY: your_api_key" "https://your-domain.com/index.php/api/clients/invoices/28"
  ```

---

### 5. Daftar Invoice Global (`/api/invoices`)
Mendapatkan daftar seluruh invoice di sistem dengan filter fleksibel.

* **URL:** `/api/invoices`
* **Query Parameters:**
  * `limit` (int, default: 50) - Maksimal data.
  * `offset` (int, default: 0) - Pagination offset.
  * `client_id` (int) - Menyaring invoice untuk klien tertentu saja.
  * `status` (string/int) - Menyaring berdasarkan status. Opsi: `draft`, `sent`, `viewed`, `paid`, `overdue`, atau angka ID status (1-4).
* **Contoh Request:**
  ```bash
  curl -i -H "X-API-KEY: your_api_key" "https://your-domain.com/index.php/api/invoices?status=overdue&limit=5"
  ```

---

### 6. Detail Invoice & Item (`/api/invoices/detail/<id>`)
Mendapatkan informasi detail lengkap satu invoice beserta seluruh rincian barang/jasa di dalamnya.

* **URL:** `/api/invoices/detail/<invoice_id>`
* **Contoh Request:**
  ```bash
  curl -i -H "X-API-KEY: your_api_key" "https://your-domain.com/index.php/api/invoices/detail/1"
  ```

---

### 7. Riwayat Pembayaran Masuk (`/api/payments`)
Mengambil log riwayat pembayaran yang diterima.

* **URL:** `/api/payments`
* **Query Parameters:**
  * `limit` (int, default: 50)
  * `offset` (int, default: 0)
  * `client_id` (int) - Menyaring pembayaran dari klien tertentu.
  * `invoice_id` (int) - Menyaring pembayaran untuk invoice tertentu.
* **Contoh Request:**
  ```bash
  curl -i -H "X-API-KEY: your_api_key" "https://your-domain.com/index.php/api/payments?limit=20"
  ```

---

### 8. Katalog Produk (`/api/products`)
Mendapatkan daftar produk/layanan standar di sistem.

* **URL:** `/api/products`
* **Query Parameters:**
  * `limit` (int, default: 50)
  * `offset` (int, default: 0)
  * `search` (string) - Mencari berdasarkan SKU, nama, atau deskripsi produk.
  * `family_id` (int) - Menyaring produk berdasarkan family ID.
* **Contoh Request:**
  ```bash
  curl -i -H "X-API-KEY: your_api_key" "https://your-domain.com/index.php/api/products?search=Laptop"
  ```

---

### 9. Daftar Penawaran / Quotes (`/api/quotes`)
Mendapatkan daftar penawaran harga (Quotes).

* **URL:** `/api/quotes`
* **Query Parameters:**
  * `limit` (int, default: 50)
  * `offset` (int, default: 0)
  * `client_id` (int)
  * `status` (string) - Opsi status: `draft`, `sent`, `viewed`, `approved`, `rejected`, `canceled`, atau integer (1-6).
* **Contoh Request:**
  ```bash
  curl -i -H "X-API-KEY: your_api_key" "https://your-domain.com/index.php/api/quotes?status=approved"
  ```

---

## Standar Response Error

Setiap kesalahan autentikasi atau sumber daya yang tidak ditemukan akan mengembalikan format respons JSON standar:

### 401 Unauthorized (Header X-API-KEY Salah/Kosong)
```json
{
    "success": false,
    "error": "Unauthorized"
}
```

### 404 Not Found (Data tidak ditemukan)
```json
{
    "success": false,
    "error": "Client not found"
}
```

### 405 Method Not Allowed (Akses selain GET)
```json
{
    "success": false,
    "error": "Method Not Allowed"
}
```
