import 'package:url_launcher/url_launcher.dart';
import '../../data/providers/api_provider.dart';
import 'home_controller.dart';
import 'materi_controller.dart';

class DetailMateriController extends GetxController {
  final ApiProvider _api = Get.find<ApiProvider>();

  late int nomorPertemuan;
  late String judulPertemuan;
  late int pertemuanId;

  var deskripsiPertemuan = ''.obs;
  var daftarTopik = <Map<String, dynamic>>[].obs;
  var isKuisEnabled = false.obs;
  var isLoading = false.obs;
  var pdfUrl = ''.obs;

  @override
  void onInit() {
    super.onInit();
    pertemuanId = Get.arguments['id'] ?? 0;
    nomorPertemuan = Get.arguments['nomor'] ?? 0;
    judulPertemuan = Get.arguments['judul'] ?? 'Detail Pertemuan';
    loadDetail();
  }

  // Memuat data detail pertemuan dan topik dari API
  void loadDetail() async {
    // Nyalakan indikator loading di UI
    isLoading.value = true;
    try {
      // Panggil API getDetailPertemuan menggunakan provider Dio
      final response = await _api.getDetailPertemuan(pertemuanId);
      final d = response.data['data'];
      
      // Ambil deskripsi dan url PDF modul dari response API
      deskripsiPertemuan.value = d['deskripsi'] ?? '';
      pdfUrl.value = d['pdf_url'] ?? '';
      
      // Map daftar topik menjadi List Map agar bisa di-render oleh ListView
      final list = (d['daftar_topik'] as List)
          .map((e) => Map<String, dynamic>.from(e))
          .toList();
      daftarTopik.value = list;
      
      // Evaluasi apakah kuis sudah layak diaktifkan
      checkKuisStatus();
    } catch (e) {
      // Cetak error jika gagal menghubungi server
      print('Gagal memuat detail: $e');
    } finally {
      // Matikan indikator loading di UI setelah selesai (berhasil/gagal)
      isLoading.value = false;
    }
  }

  // Melakukan unduh modul PDF menggunakan browser eksternal
  void unduhPdfMateri() async {
    // Jika tautan PDF dari API kosong, munculkan pemberitahuan informasi
    if (pdfUrl.value.isEmpty) {
      Get.snackbar('Informasi', 'Modul PDF belum diunggah oleh guru untuk pertemuan ini.',
        snackPosition: SnackPosition.BOTTOM,
      );
      return;
    }

    // Ubah string URL menjadi objek Uri
    final uri = Uri.parse(pdfUrl.value);
    // Jalankan browser eksternal atau PDF reader bawaan sistem HP untuk membuka file PDF
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } else {
      // Munculkan snackbar merah jika url gagal diluncurkan
      Get.snackbar('Error', 'Gagal membuka modul PDF.',
        snackPosition: SnackPosition.BOTTOM,
      );
    }
  }

  // Menandai topik selesai dibaca dan menyimpan progress ke database
  void toggleCompleteTopik(int index) async {
    // Gandakan objek topik untuk diubah statusnya
    var topik = Map<String, dynamic>.from(daftarTopik[index]);
    topik['is_completed'] = !(topik['is_completed'] as bool);

    // Refresh list agar Obx pada widget UI mengenali perubahan dan me-rebuild tampilan
    var newList = List<Map<String, dynamic>>.from(daftarTopik);
    newList[index] = topik;
    daftarTopik.value = newList;

    // Jika topik ditandai selesai (is_completed = true), simpan progress ke server
    if (topik['is_completed'] == true) {
      try {
        await _api.tandaiTopikSelesai(pertemuanId, topik['id']);
        
        // Refresh materi controller dan home controller agar data persentase diperbarui
        if (Get.isRegistered<MateriController>()) {
          Get.find<MateriController>().refreshMateri();
        }
        if (Get.isRegistered<HomeController>()) {
          Get.find<HomeController>().loadStatistik();
          Get.find<HomeController>().loadPertemuan();
        }
      } catch (e) {
        print('Gagal menyimpan progress: $e');
      }
    }

    // Evaluasi ulang apakah tombol kuis sudah boleh diaktifkan
    checkKuisStatus();
  }

  // Memeriksa status keaktifan tombol kuis evaluasi
  void checkKuisStatus() {
    if (daftarTopik.isEmpty) {
      isKuisEnabled.value = false;
      return;
    }
    // Tombol kuis aktif HANYA JIKA semua topik telah ditandai selesai (dibaca) oleh siswa
    bool allDone = daftarTopik.every((t) => t['is_completed'] == true);
    isKuisEnabled.value = allDone;
  }
}
