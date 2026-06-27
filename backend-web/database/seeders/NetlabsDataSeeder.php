<?php

namespace Database\Seeders;

use App\Models\HasilKuis;
use App\Models\ModulPdf;
use App\Models\Pertemuan;
use App\Models\SoalKuis;
use App\Models\TopikMateri;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NetlabsDataSeeder extends Seeder
{
    public function run(): void
    {
        // Kosongkan dulu (urutan penting karena foreign key)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        HasilKuis::truncate();
        SoalKuis::truncate();
        ModulPdf::truncate();
        TopikMateri::truncate();
        Pertemuan::truncate();
        DB::table('progress_siswa')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ========================================
        // SEMESTER 1 (6 Pertemuan)
        // ========================================
        $this->seedPertemuan1();
        $this->seedPertemuan2();
        $this->seedPertemuan3();
        $this->seedPertemuan4();
        $this->seedPertemuan5();
        $this->seedPertemuan6();

        // ========================================
        // SEMESTER 2 (6 Pertemuan)
        // ========================================
        $this->seedPertemuan7();
        $this->seedPertemuan8();
        $this->seedPertemuan9();
        $this->seedPertemuan10();
        $this->seedPertemuan11();
        $this->seedPertemuan12();
    }

    private function buatPertemuan($nomor, $judul, $deskripsi, $semester, $warna, array $topiks, array $soals, array $moduls = [])
    {
        $pertemuan = Pertemuan::create([
            'nomor_urut' => $nomor,
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'semester' => $semester,
            'warna_tema' => $warna,
        ]);

        foreach ($topiks as $t) {
            TopikMateri::create([
                'pertemuan_id' => $pertemuan->id,
                'judul' => $t['judul'],
                'isi_materi' => $t['isi'],
            ]);
        }

        foreach ($soals as $s) {
            SoalKuis::create([
                'pertemuan_id' => $pertemuan->id,
                'pertanyaan' => $s['pertanyaan'],
                'pilihan_a' => $s['a'],
                'pilihan_b' => $s['b'],
                'pilihan_c' => $s['c'],
                'pilihan_d' => $s['d'],
                'kunci_jawaban' => $s['kunci'],
                'penjelasan' => $s['penjelasan'] ?? null,
            ]);
        }

        foreach ($moduls as $m) {
            ModulPdf::create([
                'pertemuan_id' => $pertemuan->id,
                'file_name' => $m,
                'status_indexing' => 'success',
            ]);
        }
    }

    private function seedPertemuan1()
    {
        $this->buatPertemuan(
            1,
            'Pengenalan Jaringan Komputer',
            'Memahami konsep dasar jaringan komputer, jenis jaringan, dan topologi jaringan yang umum digunakan.',
            '1',
            '#0D9488',
            [
                [
                    'judul' => 'Definisi dan Konsep Dasar Jaringan',
                    'isi' => 'Jaringan komputer adalah dua atau lebih komputer yang saling terhubung menggunakan media komunikasi tertentu sehingga dapat saling bertukar data dan informasi. Tujuan utama jaringan komputer adalah berbagi sumber daya (resource sharing) seperti data, printer, dan koneksi internet, serta mempermudah komunikasi antar pengguna.',
                ],
                [
                    'judul' => 'Jenis Jaringan Berdasarkan Skala',
                    'isi' => 'Berdasarkan skala/luas wilayah, jaringan dibagi menjadi:\n1. PAN (Personal Area Network) - area sangat kecil, contoh Bluetooth.\n2. LAN (Local Area Network) - area terbatas seperti satu gedung/lab.\n3. MAN (Metropolitan Area Network) - area satu kota.\n4. WAN (Wide Area Network) - area sangat luas, contoh internet.',
                ],
                [
                    'judul' => 'Topologi Jaringan',
                    'isi' => 'Topologi jaringan adalah cara fisik atau logis penyusunan perangkat dalam jaringan. Topologi yang umum: Star (bintang), Bus, Ring (cincin), Mesh, dan Tree. Topologi Star paling populer di LAN karena mudah dikelola dan jika satu node mati tidak mempengaruhi yang lain.',
                ],
            ],
            [
                [
                    'pertanyaan' => 'Apa kepanjangan dari LAN dalam jaringan komputer?',
                    'a' => 'Local Access Network',
                    'b' => 'Local Area Network',
                    'c' => 'Large Area Network',
                    'd' => 'Linked Area Network',
                    'kunci' => 'B',
                    'penjelasan' => 'LAN singkatan dari Local Area Network, jaringan dengan area terbatas seperti lab atau gedung.',
                ],
                [
                    'pertanyaan' => 'Topologi jaringan yang menggunakan satu pusat (switch/hub) sebagai penghubung semua perangkat adalah?',
                    'a' => 'Topologi Bus',
                    'b' => 'Topologi Ring',
                    'c' => 'Topologi Star',
                    'd' => 'Topologi Mesh',
                    'kunci' => 'C',
                    'penjelasan' => 'Topologi Star (bintang) memiliki satu titik pusat yang menghubungkan semua perangkat.',
                ],
                [
                    'pertanyaan' => 'Manakah jaringan dengan skala paling luas?',
                    'a' => 'PAN',
                    'b' => 'LAN',
                    'c' => 'MAN',
                    'd' => 'WAN',
                    'kunci' => 'D',
                    'penjelasan' => 'WAN (Wide Area Network) mencakup area geografis sangat luas, contohnya internet.',
                ],
            ],
            ['Modul-01-Pengenalan-Jaringan.pdf']
        );
    }

    private function seedPertemuan2()
    {
        $this->buatPertemuan(
            2,
            'Model OSI dan TCP/IP',
            'Mempelajari lapisan-lapisan model referensi OSI 7 layer dan model TCP/IP beserta fungsinya.',
            '1',
            '#0F766E',
            [
                [
                    'judul' => 'Model Referensi OSI 7 Layer',
                    'isi' => 'Model OSI (Open Systems Interconnection) adalah standar referensi jaringan yang terdiri dari 7 lapisan:\n7. Application Layer\n6. Presentation Layer\n5. Session Layer\n4. Transport Layer\n3. Network Layer\n2. Data Link Layer\n1. Physical Layer\n\nSetiap layer memiliki fungsi spesifik dalam proses komunikasi data.',
                ],
                [
                    'judul' => 'Model TCP/IP',
                    'isi' => 'Model TCP/IP lebih sederhana dengan 4 lapisan:\n4. Application Layer (gabungan OSI 5-7)\n3. Transport Layer (TCP/UDP)\n2. Internet Layer (IP)\n1. Network Access Layer (gabungan OSI 1-2)\n\nTCP/IP adalah model yang dipakai internet saat ini.',
                ],
                [
                    'judul' => 'Perbandingan OSI vs TCP/IP',
                    'isi' => 'Perbedaan utama: OSI memiliki 7 layer sedangkan TCP/IP 4 layer. OSI bersifat teoritis/reference model, sedangkan TCP/IP bersifat praktis dan menjadi standar internet. Protokol TCP/IP lebih banyak dipakai di dunia nyata dibandingkan model OSI.',
                ],
            ],
            [
                [
                    'pertanyaan' => 'Layer ke-4 pada model OSI adalah?',
                    'a' => 'Network Layer',
                    'b' => 'Transport Layer',
                    'c' => 'Session Layer',
                    'd' => 'Data Link Layer',
                    'kunci' => 'B',
                    'penjelasan' => 'Layer ke-4 OSI adalah Transport Layer yang bertanggung jawab atas pengiriman data end-to-end (TCP/UDP).',
                ],
                [
                    'pertanyaan' => 'Berapa jumlah layer pada model TCP/IP?',
                    'a' => '7 Layer',
                    'b' => '6 Layer',
                    'c' => '5 Layer',
                    'd' => '4 Layer',
                    'kunci' => 'D',
                    'penjelasan' => 'Model TCP/IP terdiri dari 4 layer: Application, Transport, Internet, dan Network Access.',
                ],
            ],
            ['Modul-02-OSI-dan-TCPIP.pdf']
        );
    }

    private function seedPertemuan3()
    {
        $this->buatPertemuan(
            3,
            'Pengalamatan IP Address',
            'Memahami konsep IP Address, kelas IP (classful), dan perbedaan IPv4 dengan IPv6.',
            '1',
            '#14B8A6',
            [
                [
                    'judul' => 'Pengertian IP Address',
                    'isi' => 'IP Address (Internet Protocol Address) adalah deretan angka unik yang menjadi identitas setiap perangkat dalam jaringan. IP Address berfungsi agar perangkat dapat saling berkomunikasi dan dikenali dalam jaringan.\n\nIPv4 terdiri dari 32 bit (4 oktet), contoh: 192.168.1.1\nIPv6 terdiri dari 128 bit, contoh: 2001:0db8:85a3::8a2e:0370:7334',
                ],
                [
                    'judul' => 'Kelas IP Address (Classful)',
                    'isi' => 'IPv4 dibagi menjadi beberapa kelas:\n- Kelas A: 1.0.0.0 - 126.255.255.255 (subnet /8)\n- Kelas B: 128.0.0.0 - 191.255.255.255 (subnet /16)\n- Kelas C: 192.0.0.0 - 223.255.255.255 (subnet /24)\n- Kelas D: 224-239 (multicast)\n- Kelas E: 240-255 (eksperimen)\n\nPrivate IP: 10.x.x.x, 172.16-31.x.x, 192.168.x.x',
                ],
                [
                    'judul' => 'IPv4 vs IPv6',
                    'isi' => 'IPv4 menggunakan 32 bit sehingga hanya mampu menampung ~4.3 miliar alamat. Karena mulai habis, dikembangkan IPv6 dengan 128 bit yang mampu menampung 3.4 x 10^38 alamat. IPv6 juga memiliki keunggulan: header lebih sederhana, dukungan QoS, dan keamanan (IPsec) bawaan.',
                ],
            ],
            [
                [
                    'pertanyaan' => 'IP Address 192.168.1.10 termasuk kelas berapa?',
                    'a' => 'Kelas A',
                    'b' => 'Kelas B',
                    'c' => 'Kelas C',
                    'd' => 'Kelas D',
                    'kunci' => 'C',
                    'penjelasan' => 'IP yang diawali 192-223 termasuk Kelas C. 192.168.x.x juga merupakan private IP Kelas C.',
                ],
                [
                    'pertanyaan' => 'Berapa jumlah bit pada IPv6?',
                    'a' => '32 bit',
                    'b' => '64 bit',
                    'c' => '96 bit',
                    'd' => '128 bit',
                    'kunci' => 'D',
                    'penjelasan' => 'IPv6 menggunakan 128 bit, jauh lebih banyak dari IPv4 yang 32 bit.',
                ],
                [
                    'pertanyaan' => 'Manakah yang merupakan private IP address?',
                    'a' => '8.8.8.8',
                    'b' => '10.0.0.1',
                    'c' => '172.32.0.1',
                    'd' => '203.130.1.1',
                    'kunci' => 'B',
                    'penjelasan' => '10.0.0.0/8 adalah range private IP Kelas A. Private IP tidak bisa diakses dari internet publik.',
                ],
            ],
            ['Modul-03-IP-Address.pdf']
        );
    }

    private function seedPertemuan4()
    {
        $this->buatPertemuan(
            4,
            'Subnetting dan CIDR',
            'Belajar teknik subnetting dengan metode CIDR (Classless Inter-Domain Routing) untuk pembagian jaringan.',
            '1',
            '#0891B2',
            [
                [
                    'judul' => 'Konsep CIDR',
                    'isi' => 'CIDR (Classless Inter-Domain Routing) adalah metode pengalamatan IP tanpa batasan kelas. CIDR menggunakan notasi prefix seperti /24, /25, /26 untuk menentukan jumlah bit network.\n\nContoh: 192.168.1.0/24 berarti 24 bit pertama adalah network, 8 bit sisanya untuk host (256 IP, 254 usable).',
                ],
                [
                    'judul' => 'Tabel Subnet Mask CIDR',
                    'isi' => '/24 = 255.255.255.0 (256 IP)\n/25 = 255.255.255.128 (128 IP)\n/26 = 255.255.255.192 (64 IP)\n/27 = 255.255.255.224 (32 IP)\n/28 = 255.255.255.240 (16 IP)\n/29 = 255.255.255.248 (8 IP)\n/30 = 255.255.255.252 (4 IP)\n\nRumus jumlah host = 2^(32-prefix) - 2',
                ],
                [
                    'judul' => 'Cara Subnetting CIDR',
                    'isi' => 'Langkah subnetting CIDR:\n1. Tentukan prefix (mis. /26)\n2. Hitung jumlah host = 2^(32-26) = 2^6 = 64 IP\n3. Hitung jumlah subnet = 2^(prefix-24) = 2^2 = 4 subnet\n4. Bagi blok IP: 192.168.1.0/26, 192.168.1.64/26, 192.168.1.128/26, 192.168.1.192/26\n5. Setiap subnet: network, broadcast, dan host usable.',
                ],
            ],
            [
                [
                    'pertanyaan' => 'Prefix /26 memiliki berapa jumlah IP address tersedia?',
                    'a' => '32 IP',
                    'b' => '64 IP',
                    'c' => '128 IP',
                    'd' => '16 IP',
                    'kunci' => 'B',
                    'penjelasan' => '/26 = 2^(32-26) = 2^6 = 64 IP total (62 usable setelah dikurangi network & broadcast).',
                ],
                [
                    'pertanyaan' => 'Subnet mask dari prefix /24 adalah?',
                    'a' => '255.255.255.128',
                    'b' => '255.255.255.0',
                    'c' => '255.255.0.0',
                    'd' => '255.255.255.192',
                    'kunci' => 'B',
                    'penjelasan' => '/24 sama dengan subnet mask 255.255.255.0 (kelas C standar), 256 IP total.',
                ],
                [
                    'pertanyaan' => 'Jumlah host usable pada prefix /30 adalah?',
                    'a' => '4',
                    'b' => '3',
                    'c' => '2',
                    'd' => '1',
                    'kunci' => 'C',
                    'penjelasan' => '/30 = 4 IP total, dikurangi network & broadcast = 2 host usable. Prefix /30 biasanya dipakai untuk link point-to-point router.',
                ],
            ],
            ['Modul-04-CIDR-Subnetting.pdf']
        );
    }

    private function seedPertemuan5()
    {
        $this->buatPertemuan(
            5,
            'VLSM (Variable Length Subnet Mask)',
            'Teknik subnetting lanjutan dengan VLSM untuk alokasi IP yang efisien sesuai kebutuhan host tiap subnet.',
            '1',
            '#06B6D4',
            [
                [
                    'judul' => 'Konsep VLSM',
                    'isi' => 'VLSM (Variable Length Subnet Mask) adalah teknik subnetting yang memungkinkan penggunaan prefix yang berbeda-beda dalam satu jaringan utama. Tujuannya untuk efisiensi alokasi IP agar tidak ada IP yang terbuang.\n\nBerbeda dengan CIDR yang membagi sama rata, VLSM membagi sesuai kebutuhan host tiap subnet.',
                ],
                [
                    'judul' => 'Langkah Menghitung VLSM',
                    'isi' => '1. Urutkan kebutuhan host dari yang TERBESAR ke TERKECIL.\n2. Untuk setiap kebutuhan, cari prefix yang sesuai:\n   - 100 host -> /25 (126 usable)\n   - 50 host -> /26 (62 usable)\n   - 25 host -> /27 (30 usable)\n   - 10 host -> /28 (14 usable)\n   - 2 host -> /30 (2 usable)\n3. Alokasikan blok IP secara berurutan.\n4. Catat network, broadcast, dan range tiap subnet.',
                ],
                [
                    'judul' => 'Contoh Kasus VLSM',
                    'isi' => 'Diketahui network 192.168.1.0/24 dengan kebutuhan:\n- LAN A: 60 host -> /26 (192.168.1.0 - 192.168.1.63)\n- LAN B: 30 host -> /27 (192.168.1.64 - 192.168.1.95)\n- LAN C: 10 host -> /28 (192.168.1.96 - 192.168.1.111)\n- Link Router: 2 host -> /30 (192.168.1.112 - 192.168.1.115)\n\nSisa IP 192.168.1.116 - 192.168.1.255 masih tersedia untuk subnet lain.',
                ],
            ],
            [
                [
                    'pertanyaan' => 'Apa tujuan utama dari VLSM?',
                    'a' => 'Mempercepat koneksi internet',
                    'b' => 'Membagi jaringan sama rata',
                    'c' => 'Efisiensi alokasi IP address',
                    'd' => 'Mengamankan jaringan dari hacker',
                    'kunci' => 'C',
                    'penjelasan' => 'VLSM bertujuan agar alokasi IP efisien sesuai kebutuhan host, tidak ada IP terbuang.',
                ],
                [
                    'pertanyaan' => 'Untuk kebutuhan 50 host, prefix VLSM yang tepat adalah?',
                    'a' => '/25',
                    'b' => '/26',
                    'c' => '/27',
                    'd' => '/28',
                    'kunci' => 'B',
                    'penjelasan' => '/26 memiliki 62 usable host, cukup untuk 50 host. /27 hanya 30 usable, kurang.',
                ],
            ],
            ['Modul-05-VLSM.pdf']
        );
    }

    private function seedPertemuan6()
    {
        $this->buatPertemuan(
            6,
            'Kabel Jaringan dan Konektor',
            'Praktik pembuatan kabel UTP straight dan crossover dengan konektor RJ-45.',
            '1',
            '#0EA5E9',
            [
                [
                    'judul' => 'Jenis Kabel UTP',
                    'isi' => 'Kabel UTP (Unshielded Twisted Pair) adalah kabel jaringan paling umum di LAN. Terdiri dari 8 kabel kecil yang berpasangan (twisted pair).\n\nKategori UTP:\n- Cat 5e: mendukung hingga 1 Gbps\n- Cat 6: mendukung hingga 10 Gbps (jarak pendek)\n- Cat 6a: 10 Gbps hingga 100 meter\n- Cat 7: 10 Gbps+ dengan shielding',
                ],
                [
                    'judul' => 'Urutan Kabel Straight',
                    'isi' => 'Kabel straight dipakai untuk menghubungkan perangkat berbeda jenis (PC ke Switch). Kedua ujung kabel memiliki urutan sama (T568A atau T568B).\n\nUrutan T568B (paling umum):\n1. Putih-Orange\n2. Orange\n3. Putih-Hijau\n4. Biru\n5. Putih-Biru\n6. Hijau\n7. Putih-Coklat\n8. Coklat',
                ],
                [
                    'judul' => 'Urutan Kabel Crossover',
                    'isi' => 'Kabel crossover dipakai untuk menghubungkan perangkat sejenis (PC ke PC, Switch ke Switch). Satu ujung T568A, ujung lain T568B.\n\nUjung A (T568A):\n1. Putih-Hijau\n2. Hijau\n3. Putih-Orange\n4. Biru\n5. Putih-Biru\n6. Orange\n7. Putih-Coklat\n8. Coklat\n\nUjung B (T568B): urutan straight standar.',
                ],
            ],
            [
                [
                    'pertanyaan' => 'Kabel straight digunakan untuk menghubungkan?',
                    'a' => 'PC ke PC',
                    'b' => 'Switch ke Switch',
                    'c' => 'PC ke Switch',
                    'd' => 'Router ke Router',
                    'kunci' => 'C',
                    'penjelasan' => 'Kabel straight menghubungkan perangkat berbeda jenis, seperti PC ke Switch atau Router ke Switch.',
                ],
                [
                    'pertanyaan' => 'Pada kabel straight T568B, kabel pin nomor 1 berwarna?',
                    'a' => 'Putih-Hijau',
                    'b' => 'Putih-Orange',
                    'c' => 'Orange',
                    'd' => 'Biru',
                    'kunci' => 'B',
                    'penjelasan' => 'Urutan T568B pin 1 adalah Putih-Orange, pin 2 Orange, pin 3 Putih-Hijau, dst.',
                ],
            ],
            ['Modul-06-Kabel-UTP.pdf']
        );
    }

    // ========================================
    // SEMESTER 2
    // ========================================

    private function seedPertemuan7()
    {
        $this->buatPertemuan(
            7,
            'Konfigurasi Router Statis',
            'Praktik konfigurasi routing statis pada router Cisco menggunakan static route untuk menghubungkan jaringan berbeda.',
            '2',
            '#0D9488',
            [
                [
                    'judul' => 'Konsep Routing Statis',
                    'isi' => 'Routing statis adalah metode routing di mana jalur (route) ke jaringan tujuan dimasukkan secara manual oleh administrator. Cocok untuk jaringan kecil yang topologinya jarang berubah.\n\nKelebihan: tidak memakan resource router, aman, prediksi rute jelas.\nKekurangan: tidak otomatis adaptif jika ada perubahan topologi.',
                ],
                [
                    'judul' => 'Perintah Static Route Cisco IOS',
                    'isi' => 'Perintah dasar static route pada Cisco:\n\nip route <network_tujuan> <subnet_mask> <next_hop>\n\nContoh:\nip route 192.168.2.0 255.255.255.0 10.0.0.2\n\nArtinya: untuk mencapai network 192.168.2.0/24, kirim paket ke next-hop 10.0.0.2.\n\nDefault route: ip route 0.0.0.0 0.0.0.0 10.0.0.2',
                ],
            ],
            [
                [
                    'pertanyaan' => 'Kapan penggunaan routing statis paling tepat?',
                    'a' => 'Jaringan skala besar dengan topologi sering berubah',
                    'b' => 'Jaringan kecil dengan topologi jarang berubah',
                    'c' => 'Jaringan yang butuh konvergensi cepat',
                    'd' => 'Jaringan dengan banyak ISP',
                    'kunci' => 'B',
                    'penjelasan' => 'Routing statis cocok untuk jaringan kecil yang stabil karena route dimasukkan manual.',
                ],
                [
                    'pertanyaan' => 'Perintah untuk membuat default route pada Cisco adalah?',
                    'a' => 'ip route 0.0.0.0 0.0.0.0 10.0.0.2',
                    'b' => 'ip default-route 10.0.0.2',
                    'c' => 'route 0.0.0.0 via 10.0.0.2',
                    'd' => 'default-gateway 10.0.0.2',
                    'kunci' => 'A',
                    'penjelasan' => 'Default route pakai ip route 0.0.0.0 0.0.0.0 <next_hop>, berarti semua traffic tidak dikenal dikirim ke next-hop.',
                ],
            ],
            ['Modul-07-Static-Routing.pdf']
        );
    }

    private function seedPertemuan8()
    {
        $this->buatPertemuan(
            8,
            'Routing Dinamis (OSPF)',
            'Memahami protocol routing dinamis OSPF (Open Shortest Path First) dan konfigurasinya.',
            '2',
            '#0F766E',
            [
                [
                    'judul' => 'Konsep OSPF',
                    'isi' => 'OSPF (Open Shortest Path First) adalah protocol routing dinamis bertipe Link-State yang menggunakan algoritma Dijkstra (SPF). OSPF menghitung jalur terpendek berdasarkan cost (bandwidth).\n\nKelebihan: konvergensi cepat, skalabel, mendukung VLSM.\nOSPF menggunakan area untuk membatasi penyebaran LSA. Area 0 (backbone) wajib ada.',
                ],
                [
                    'judul' => 'Konfigurasi OSPF Dasar',
                    'isi' => 'Perintah konfigurasi OSPF pada Cisco IOS:\n\nrouter ospf 1\nnetwork 192.168.1.0 0.0.0.255 area 0\nnetwork 10.0.0.0 0.0.0.3 area 0\n\nCatatan: OSPF menggunakan wildcard mask (kebalikan subnet mask).\nSubnet 255.255.255.0 -> wildcard 0.0.0.255\nProcess ID (1) hanya lokal significance, boleh beda antar router.',
                ],
            ],
            [
                [
                    'pertanyaan' => 'Algoritma apa yang digunakan OSPF untuk mencari jalur terpendek?',
                    'a' => 'Bellman-Ford',
                    'b' => 'Dijkstra (SPF)',
                    'c' => 'Distance Vector',
                    'd' => 'Path Vector',
                    'kunci' => 'B',
                    'penjelasan' => 'OSPF bertipe Link-State dan menggunakan algoritma Dijkstra (Shortest Path First).',
                ],
                [
                    'pertanyaan' => 'Wildcard mask dari subnet 255.255.255.0 adalah?',
                    'a' => '0.0.0.255',
                    'b' => '255.255.255.0',
                    'c' => '0.255.255.255',
                    'd' => '255.0.0.0',
                    'kunci' => 'A',
                    'penjelasan' => 'Wildcard mask adalah kebalikan subnet mask. 255.255.255.0 -> 0.0.0.255.',
                ],
            ],
            ['Modul-08-OSPF.pdf']
        );
    }

    private function seedPertemuan9()
    {
        $this->buatPertemuan(
            9,
            'DHCP Server Configuration',
            'Konfigurasi DHCP Server untuk pembagian IP otomatis pada client dalam jaringan.',
            '2',
            '#14B8A6',
            [
                [
                    'judul' => 'Konsep DHCP',
                    'isi' => 'DHCP (Dynamic Host Configuration Protocol) adalah protocol yang memberikan konfigurasi IP secara otomatis ke client. DHCP bekerja dengan model client-server menggunakan UDP port 67 (server) dan 68 (client).\n\nPesan DORA:\n1. Discover - client cari DHCP server\n2. Offer - server tawarkan IP\n3. Request - client minta IP yang ditawarkan\n4. Acknowledge - server konfirmasi',
                ],
                [
                    'judul' => 'Konfigurasi DHCP Cisco',
                    'isi' => 'Konfigurasi DHCP pool pada router Cisco:\n\nip dhcp pool LAB-TKJ\nnetwork 192.168.1.0 255.255.255.0\ndefault-router 192.168.1.1\ndns-server 8.8.8.8\nlease 7\n\nUntuk exclude IP tertentu:\nip dhcp excluded-address 192.168.1.1 192.168.1.10',
                ],
            ],
            [
                [
                    'pertanyaan' => 'Port yang digunakan DHCP server adalah?',
                    'a' => 'TCP 67',
                    'b' => 'UDP 67',
                    'c' => 'UDP 53',
                    'd' => 'TCP 80',
                    'kunci' => 'B',
                    'penjelasan' => 'DHCP server menggunakan UDP port 67, client UDP port 68. DHCP berbasis UDP bukan TCP.',
                ],
                [
                    'pertanyaan' => 'Urutan proses DHCP yang benar adalah?',
                    'a' => 'Offer, Discover, Acknowledge, Request',
                    'b' => 'Discover, Offer, Request, Acknowledge',
                    'c' => 'Request, Offer, Discover, Acknowledge',
                    'd' => 'Discover, Request, Offer, Acknowledge',
                    'kunci' => 'B',
                    'penjelasan' => 'Urutan DORA: Discover (client) -> Offer (server) -> Request (client) -> Acknowledge (server).',
                ],
            ],
            ['Modul-09-DHCP.pdf']
        );
    }

    private function seedPertemuan10()
    {
        $this->buatPertemuan(
            10,
            'NAT (Network Address Translation)',
            'Memahami dan mengkonfigurasi NAT pada router untuk menerjemahkan private IP ke public IP.',
            '2',
            '#0891B2',
            [
                [
                    'judul' => 'Konsep NAT',
                    'isi' => 'NAT (Network Address Translation) adalah teknik menerjemahkan alamat IP private menjadi IP public agar bisa mengakses internet. NAT bekerja di router/gateway.\n\nJenis NAT:\n1. Static NAT - 1 private IP dipetakan ke 1 public IP\n2. Dynamic NAT - private IP dipetakan ke pool public IP\n3. PAT (NAT Overload) - banyak private IP dipetakan ke 1 public IP dengan port berbeda (paling umum)',
                ],
                [
                    'judul' => 'Konfigurasi PAT Cisco',
                    'isi' => 'Konfigurasi NAT Overload (PAT) pada Cisco:\n\naccess-list 1 permit 192.168.1.0 0.0.0.255\nip nat inside source list 1 interface fa0/1 overload\n\ninterface fa0/0\nip nat inside\n\ninterface fa0/1\nip nat outside\n\nfa0/0 = interface ke LAN (inside)\nfa0/1 = interface ke ISP (outside)',
                ],
            ],
            [
                [
                    'pertanyaan' => 'Jenis NAT yang memetakan banyak private IP ke 1 public IP disebut?',
                    'a' => 'Static NAT',
                    'b' => 'Dynamic NAT',
                    'c' => 'PAT (NAT Overload)',
                    'd' => 'DNAT',
                    'kunci' => 'C',
                    'penjelasan' => 'PAT (Port Address Translation) / NAT Overload memetakan banyak private IP ke 1 public IP menggunakan nomor port sebagai pembeda.',
                ],
                [
                    'pertanyaan' => 'Apa fungsi utama dari NAT?',
                    'a' => 'Mempercepat koneksi internet',
                    'b' => 'Menerjemahkan private IP ke public IP',
                    'c' => 'Memblokir akses hacker',
                    'd' => 'Membagi bandwidth',
                    'kunci' => 'B',
                    'penjelasan' => 'NAT menerjemahkan private IP (tidak bisa diakses internet) menjadi public IP agar bisa online.',
                ],
            ],
            ['Modul-10-NAT.pdf']
        );
    }

    private function seedPertemuan11()
    {
        $this->buatPertemuan(
            11,
            'VLAN dan Trunking',
            'Membagi jaringan fisik menjadi beberapa jaringan logis menggunakan VLAN dan konfigurasi trunk antar switch.',
            '2',
            '#06B6D4',
            [
                [
                    'judul' => 'Konsep VLAN',
                    'isi' => 'VLAN (Virtual LAN) adalah teknologi membagi satu switch fisik menjadi beberapa jaringan logis terpisah. VLAN meningkatkan keamanan dan efisiensi dengan mengisolasi broadcast domain.\n\nContoh:\n- VLAN 10: Departemen Marketing\n- VLAN 20: Departemen IT\n- VLAN 30: Departemen HRD\n\nHost di VLAN berbeda tidak bisa saling komunikasi tanpa router (inter-VLAN routing).',
                ],
                [
                    'judul' => 'Konfigurasi VLAN dan Trunk',
                    'isi' => 'Membuat VLAN:\nvlan 10\nname Marketing\n\nMenetapkan port access:\ninterface fa0/1\nswitchport mode access\nswitchport access vlan 10\n\nMembuat port trunk (antar switch):\ninterface fa0/24\nswitchport mode trunk\nswitchport trunk allowed vlan 10,20,30',
                ],
            ],
            [
                [
                    'pertanyaan' => 'Apa manfaat utama dari VLAN?',
                    'a' => 'Mempercepat transfer data',
                    'b' => 'Mengisolasi broadcast domain dan meningkatkan keamanan',
                    'c' => 'Mengurangi biaya kabel',
                    'd' => 'Menambah bandwidth internet',
                    'kunci' => 'B',
                    'penjelasan' => 'VLAN mengisolasi broadcast domain sehingga traffic broadcast tidak menyebar ke seluruh jaringan, juga meningkatkan keamanan.',
                ],
                [
                    'pertanyaan' => 'Port yang menghubungkan antar switch dan membawa banyak VLAN disebut?',
                    'a' => 'Access port',
                    'b' => 'Trunk port',
                    'c' => 'Native port',
                    'd' => 'Hybrid port',
                    'kunci' => 'B',
                    'penjelasan' => 'Trunk port membawa traffic multiple VLAN antar switch, menggunakan encapsulation 802.1Q.',
                ],
            ],
            ['Modul-11-VLAN-Trunking.pdf']
        );
    }

    private function seedPertemuan12()
    {
        $this->buatPertemuan(
            12,
            'ACL (Access Control List)',
            'Konfigurasi Access Control List untuk filtering traffic dan keamanan jaringan.',
            '2',
            '#0EA5E9',
            [
                [
                    'judul' => 'Konsep ACL',
                    'isi' => 'ACL (Access Control List) adalah daftar aturan (rules) yang diterapkan pada router/switch untuk mengizinkan (permit) atau menolak (deny) traffic berdasarkan kriteria seperti IP source/destination, port, atau protocol.\n\nJenis ACL:\n1. Standard ACL (1-99, 1300-1999) - filter berdasarkan source IP saja\n2. Extended ACL (100-199, 2000-2699) - filter berdasarkan source, destination, port, protocol',
                ],
                [
                    'judul' => 'Konfigurasi ACL Extended',
                    'isi' => 'Contoh ACL Extended untuk blokir akses HTTP dari LAN ke server tertentu:\n\naccess-list 100 deny tcp 192.168.1.0 0.0.0.255 host 10.0.0.5 eq 80\naccess-list 100 permit ip any any\n\nTerapkan ke interface:\ninterface fa0/0\nip access-group 100 in\n\nAturan dievaluasi berurutan, ada implicit deny di akhir.',
                ],
            ],
            [
                [
                    'pertanyaan' => 'Standard ACL memfilter traffic berdasarkan?',
                    'a' => 'Source IP saja',
                    'b' => 'Destination IP saja',
                    'c' => 'Source dan Destination IP',
                    'd' => 'Port dan Protocol',
                    'kunci' => 'A',
                    'penjelasan' => 'Standard ACL (1-99) hanya memfilter berdasarkan source IP address. Extended ACL yang bisa filter source, destination, port, dan protocol.',
                ],
                [
                    'pertanyaan' => 'Range nomor ACL untuk Extended ACL adalah?',
                    'a' => '1-99',
                    'b' => '100-199',
                    'c' => '200-299',
                    'd' => '500-599',
                    'kunci' => 'B',
                    'penjelasan' => 'Extended ACL menggunakan range 100-199 (atau 2000-2699 expanded). Standard ACL 1-99.',
                ],
            ],
            ['Modul-12-ACL.pdf']
        );
    }
}
