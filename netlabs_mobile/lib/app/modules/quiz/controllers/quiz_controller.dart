import 'package:get/get.dart';

class QuizController extends GetxController {
  // Daftar soal kuis simulasi seputar Jaringan Komputer Dasar
  final List<Map<String, String>> daftarSoal = [
    {
      "pertanyaan": "Berapakah jumlah host yang dapat digunakan pada subnet dengan subnet mask 255.255.255.240 (/28)?",
      "A": "14 host",
      "B": "16 host",
      "C": "30 host",
      "D": "32 host",
      "kunci": "A"
    },
    {
      "pertanyaan": "Metode alokasi alamat IP yang membagi jaringan menjadi beberapa subnet dengan ukuran berbeda sesuai kebutuhan host disebut...",
      "A": "Classful Addressing",
      "B": "Variable Length Subnet Mask (VLSM)",
      "C": "Classless Inter-Domain Routing (CIDR)",
      "D": "Network Address Translation (NAT)",
      "kunci": "B"
    },
    {
      "pertanyaan": "Pada kabel UTP kategori 5e/6, urutan kabel untuk standar T568A dimulai dengan warna...",
      "A": "Putih Oranye",
      "B": "Putih Hijau",
      "C": "Putih Biru",
      "D": "Putih Cokelat",
      "kunci": "B"
    },
    {
      "pertanyaan": "Protokol yang berfungsi untuk memberikan konfigurasi alamat IP secara otomatis kepada perangkat klien di dalam jaringan adalah...",
      "A": "DNS",
      "B": "DHCP",
      "C": "FTP",
      "D": "HTTP",
      "kunci": "B"
    },
    {
      "pertanyaan": "Jika Anda melakukan pengujian koneksi menggunakan perintah 'ping' ke gateway lokal dan menerima hasil 'Request Timed Out' (RTO), penyebab paling umum adalah...",
      "A": "Alamat IP gateway bentrok dengan IP publik",
      "B": "Kabel jaringan terputus atau konfigurasi IP perangkat tidak se-subnet dengan gateway",
      "C": "Domain Name System (DNS) server sedang tidak aktif",
      "D": "Server web tujuan sedang mengalami kelebihan beban (overload)",
      "kunci": "B"
    }
  ];

  var currentQuestionIndex = 0.obs;
  var selectedJawaban = "".obs;
  var isQuizFinished = false.obs;
  var nilaiAkhir = 0.0.obs;
  var rekomendasiAi = "".obs;

  final _jumlahBenar = 0.obs;
  int get jumlahBenar => _jumlahBenar.value;


  void selectOption(String key) {
    selectedJawaban.value = key;
  }

  void nextQuestion() {
    if (selectedJawaban.value.isEmpty) return;

    // Cek kecocokan jawaban dengan kunci
    var soalSekarang = daftarSoal[currentQuestionIndex.value];
    if (selectedJawaban.value == soalSekarang['kunci']) {
      _jumlahBenar.value++;
    }

    // Reset pilihan jawaban untuk soal berikutnya
    selectedJawaban.value = "";

    // Navigasi soal berikutnya atau selesaikan kuis
    if (currentQuestionIndex.value < daftarSoal.length - 1) {
      currentQuestionIndex.value++;
    } else {
      hitungNilaiAkhir();
      isQuizFinished.value = true;
    }
  }

  void hitungNilaiAkhir() {
    nilaiAkhir.value = (_jumlahBenar.value / daftarSoal.length) * 100;

    // Evaluasi edukatif & Rekomendasi AI Tutor
    if (nilaiAkhir.value == 100) {
      rekomendasiAi.value = "Luar biasa! Anda telah memahami seluruh konsep praktikum Jaringan Komputer dengan sangat baik. Pertahankan prestasi Anda dan tetap konsisten belajar!";
    } else if (nilaiAkhir.value >= 70) {
      rekomendasiAi.value = "Hasil yang baik! Anda telah memahami sebagian besar konsep. Untuk memaksimalkan pemahaman, kami merekomendasikan Anda meninjau kembali modul Subnetting VLSM dan konfigurasi IP Address dasar.";
    } else {
      rekomendasiAi.value = "Upaya yang bagus! Namun, Anda perlu meninjau ulang materi praktikum Jaringan Komputer, khususnya pada bagian subnetting dan standar kabel UTP. Silakan berkonsultasi dengan AI Tutor untuk penjelasan lebih detail.";
    }
  }
}
