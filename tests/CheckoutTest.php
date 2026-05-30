<?php
use PHPUnit\Framework\TestCase;
use App\Checkout;

class CheckoutTest extends TestCase{
    private $seedFile = __DIR__ . '/../data/products_seed.json';
    private $testFile = __DIR__ . '/../data/products_test.json'; // Lingkungan sementara
    private $orderFile = __DIR__ . '/../data/orders_test.json'; // Lingkungan sementara
    private $checkout;

    // CT Stage: Environment Setup (Menyiapkan data segar SEBELUM tiap tes)
    protected function setUp(): void{
        copy($this->seedFile, $this->testFile);
        file_put_contents($this->orderFile, json_encode([]));
        $this->checkout = new Checkout($this->testFile, $this->orderFile);
    }

    // CT Stage: Integration Test
    public function testCheckoutReducesStock(){
        $keranjang = ['PRD-002' => 1]; // Beli 1 Celana Jeans
        $this->checkout->prosesCheckout('test@mail.com', 'Jl. Sudirman', $keranjang);

        $products = json_decode(file_get_contents($this->testFile), true);
        $this->assertEquals(4, $products['PRD-002']['stok']); // Ekspektasi stok sisa 4
    }

    // CT Stage: Environment Cleanup (Menghapus data sampah SETELAH tiap tes)
    protected function tearDown(): void{
        if (file_exists($this->testFile)) unlink($this->testFile);
        if (file_exists($this->orderFile)) unlink($this->orderFile);
    }
}


// use PHPUnit\Framework\TestCase;
// use App\Checkout; // Menyertakan namespace dari src/Checkout.php

// require_once 'src/Checkout.php';

// class CheckoutTest extends TestCase
// {
//     private $checkout;
//     private $fileProdukDummy = 'data/products_test.json';
//     private $filePesananDummy = 'data/orders_test.json';

//     // FASE 3: Setup sebelum setiap test (Isolasi data agar aman)
//     protected function setUp(): void
//     {
//         if (!is_dir('data')) {
//             mkdir('data');
//         }

//         // Data Produk disesuaikan dengan tema Web Toko Online kamu
//         $mockProducts = [
//             'PRD-001' => ['nama' => 'Kemeja Flanel', 'harga' => 150000, 'stok' => 10],
//             'PRD-002' => ['nama' => 'Celana Jeans', 'harga' => 250000, 'stok' => 5],
//             'PRD-003' => ['nama' => 'Topi Baseball', 'harga' => 1200000, 'stok' => 2]
//         ];
        
//         file_put_contents($this->fileProdukDummy, json_encode($mockProducts, JSON_PRETTY_PRINT));
//         file_put_contents($this->filePesananDummy, json_encode([]));

//         // Inisialisasi objek dengan parameter file dummy sesuai constructor asli
//         $this->checkout = new Checkout($this->fileProdukDummy, $this->filePesananDummy);
//     }

//     // Membersihkan sisa file dummy setelah test selesai agar tidak mengotori proyek
//     protected function tearDown(): void
//     {
//         if (file_exists($this->fileProdukDummy)) unlink($this->fileProdukDummy);
//         if (file_exists($this->filePesananDummy)) unlink($this->filePesananDummy);
//     }

//     // =========================================================================
//     // FASE 2 & 3: MATRIKS & EKSEKUSI TEST CASES (MENGEJAR COVERAGE 100%)
//     // =========================================================================

//     // TC-CK-01: Validasi jika keranjang kosong (Memicu Exception 1)
//     public function testKeranjangKosongMengeluarkanException()
//     {
//         $this->expectException(Exception::class);
//         $this->expectExceptionMessage("Keranjang belanja kosong.");

//         $this->checkout->prosesCheckout('widya@gmail.com', 'Madiun', []);
//     }

//     // TC-CK-02: Validasi jika alamat kosong (Memicu Exception 2)
//     public function testAlamatKosongMengeluarkanException()
//     {
//         $this->expectException(Exception::class);
//         $this->expectExceptionMessage("Alamat pengiriman wajib diisi.");

//         $this->checkout->prosesCheckout('widya@gmail.com', '', ['PRD-001' => 1]);
//     }

//     // TC-CK-03: Validasi jika kuantitas item <= 0 (Memicu Exception 3)
//     public function testKuantitasNolAtauNegatifMengeluarkanException()
//     {
//         $this->expectException(Exception::class);
//         $this->expectExceptionMessage("Kuantitas harus lebih dari 0.");

//         $this->checkout->prosesCheckout('widya@gmail.com', 'Madiun', ['PRD-001' => 0]);
//     }

//     // TC-CK-04: Validasi jika produk tidak ada di database JSON (Memicu Exception 4)
//     public function testProdukTidakValidMengeluarkanException()
//     {
//         $this->expectException(Exception::class);
//         $this->expectExceptionMessage("Produk tidak valid.");

//         $this->checkout->prosesCheckout('widya@gmail.com', 'Madiun', ['PRODUK_GAIB' => 1]);
//     }

//     // TC-CK-05: Validasi jika stok barang di toko tidak cukup (Memicu Exception 5)
//     public function testStokTidakCukupMengeluarkanException()
//     {
//         $this->expectException(Exception::class);
//         $this->expectExceptionMessage("Stok Kemeja Flanel tidak mencukupi.");

//         $this->checkout->prosesCheckout('widya@gmail.com', 'Madiun', ['PRD-001' => 99]);
//     }

//     // TC-CK-06: Belanja murah (<= 500rb), harus bayar Ongkir 20rb & diskon 0
//     public function testCheckoutTanpaDiskonDanBayarOngkir()
//     {
//         // 2 x Kemeja Flanel = 2 x 150.000 = 300.000 (di bawah 500 ribu)
//         $keranjang = ['PRD-001' => 2]; 
//         $result = $this->checkout->prosesCheckout('widya@gmail.com', 'Madiun', $keranjang);

//         $this->assertIsArray($result);
//         // Total Akhir = 300.000 - 0 (diskon) + 20.000 (ongkir) = 320.000
//         $this->assertEquals(320000, $result['total_bayar']);
//     }

//     // TC-CK-07: Belanja menengah (> 500rb tapi <= 1jt), Gratis Ongkir & diskon 0
//     public function testCheckoutGratisOngkirTanpaDiskon()
//     {
//         // 4 x Kemeja Flanel = 4 x 150.000 = 600.000 (di atas 500 ribu)
//         $keranjang = ['PRD-001' => 4]; 
//         $result = $this->checkout->prosesCheckout('widya@gmail.com', 'Madiun', $keranjang);

//         // Total Akhir = 600.000 - 0 (diskon) + 0 (ongkir) = 600.000
//         $this->assertEquals(600000, $result['total_bayar']);
//     }

//     // TC-CK-08: Belanja besar (> 1jt), Gratis Ongkir & Dapat Potongan Diskon 10%
//     public function testCheckoutGratisOngkirDanDapatDiskonSepuluhPersen()
//     {
//         // 1 x Jaket Parka Premium = 1 x 1.200.000 = 1.200.000 (di atas 1 juta)
//         $keranjang = ['PRD-003' => 1]; 
//         $result = $this->checkout->prosesCheckout('widya@gmail.com', 'Madiun', $keranjang);

//         // Diskon = 1.200.000 * 10% = 120.000
//         // Total Akhir = (1.200.000 - 120.000) + 0 (ongkir) = 1.080.000
//         $this->assertEquals(1080000, $result['total_bayar']);
//         $this->assertEquals('Menunggu Pembayaran', $result['status']);
//     }
// }