import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import '../../../data/providers/api_provider.dart';
import '../../../routes/app_pages.dart';

class HomeController extends GetxController {
  final storage = GetStorage();
  final ApiProvider _api = Get.find<ApiProvider>();

  // Identitas siswa
  var studentName = ''.obs;
  var studentClass = ''.obs;
  var greeting = 'Selamat Belajar'.obs;

  // Statistik belajar
  var totalPertemuanSelesai = 0.obs;
  var totalPertemuan = 0.obs;
  var rataRataNilai = 0.0.obs;
  var totalTopikSelesai = 0.obs;
  var totalTopik = 0.obs;
  var totalChatAI = 0.obs;

  // Progress semester (0.0 - 1.0)
  var progressSemester = 0.0.obs;

  // Data pertemuan aktif & semua pertemuan
  var pertemuanAktif = <Map<String, dynamic>>[].obs;
  var semuaPertemuan = <Map<String, dynamic>>[].obs;

  // Kuis yang belum dikerjakan (pertemuan selesai tapi belum kuis)
  var kuisBelumDikerjakan = <Map<String, dynamic>>[].obs;

  final lanjutBelajar = Rxn<Map<String, dynamic>>();

  var isLoading = false.obs;

  @override
  void onInit() {
    super.onInit();
    studentName.value = storage.read('nama') ?? 'Siswa';
    studentClass.value = storage.read('kelas') ?? '-';
    updateGreeting();
    loadDashboard();
  }

  void updateGreeting() {
    final hour = DateTime.now().hour;
    if (hour < 11) {
      greeting.value = 'Selamat Pagi';
    } else if (hour < 15) {
      greeting.value = 'Selamat Siang';
    } else if (hour < 19) {
      greeting.value = 'Selamat Sore';
    } else {
      greeting.value = 'Selamat Malam';
    }
  }

  void loadDashboard() async {
    isLoading.value = true;
    loadStatistik();
    loadPertemuan();
    isLoading.value = false;
  }


  void loadStatistik() async {
    try {
      final response = await _api.getStatistikSiswa();
      final s = response.data['data']['statistik'];
      totalPertemuanSelesai.value = s['total_pertemuan_selesai'];
      totalPertemuan.value = s['total_pertemuan'];
      totalTopikSelesai.value = s['total_topik_selesai'];
      totalTopik.value = s['total_topik'];
      rataRataNilai.value = (s['rata_rata_nilai'] as num).toDouble();
      totalChatAI.value = s['total_chat_ai'];

      // Hitung progress semester berdasarkan topik selesai
      if (totalTopik.value > 0) {
        progressSemester.value = totalTopikSelesai.value / totalTopik.value;
      }
    } catch (e) {
      print('Gagal memuat statistik: $e');
    }
  }

  void loadPertemuan() async {
    try {
      final response = await _api.getPertemuan();
      final data = response.data['data'];

      // Gabungkan semester 1 & 2
      final s1 = (data['1'] as List).map((e) => Map<String, dynamic>.from(e)).toList();
      final s2 = (data['2'] as List).map((e) => Map<String, dynamic>.from(e)).toList();
      semuaPertemuan.value = [...s1, ...s2];

      // Pertemuan aktif: progress > 0 dan < 1
      pertemuanAktif.value = semuaPertemuan
          .where((p) => (p['progress'] as num) > 0 && (p['progress'] as num) < 1)
          .toList();

      // Lanjut belajar: pertemuan aktif pertama (yang paling dekat selesai)
      if (pertemuanAktif.isNotEmpty) {
        lanjutBelajar.value = Map<String, dynamic>.from(pertemuanAktif.first);
      } else {
        // Kalau gak ada yang aktif, ambil pertemuan pertama yang belum dimulai
        final belumMulai = semuaPertemuan.where((p) => (p['progress'] as num) == 0).toList();
        if (belumMulai.isNotEmpty) {
          lanjutBelajar.value = Map<String, dynamic>.from(belumMulai.first);
        }
      }

      // Kuis belum dikerjakan: pertemuan yang progress-nya 100% (semua topik selesai)
      // tapi belum ada hasil kuis. Untuk simplifikasi, tampilkan pertemuan selesai.
      kuisBelumDikerjakan.value = semuaPertemuan
          .where((p) => (p['progress'] as num) >= 1.0)
          .toList();
    } catch (e) {
      print('Gagal memuat pertemuan: $e');
    }
  }

  void bukaPertemuan(Map<String, dynamic> pertemuan) {
    Get.toNamed('/detail-materi', arguments: {
      'id': pertemuan['id'],
      'nomor': pertemuan['nomor'],
      'judul': pertemuan['judul'],
    });
  }

  void bukaMateri() {
    Get.toNamed('/materi');
  }

  void bukaChatbot() {
    Get.toNamed('/chatbot');
  }

  void bukaQuiz(int pertemuanId) {
    Get.toNamed('/quiz', arguments: {'pertemuan_id': pertemuanId});
  }

  void logout() async {
    try {
      await _api.logout();
    } catch (_) {}
    storage.remove('token');
    storage.remove('nama');
    storage.remove('kelas');
    storage.remove('role');
    Get.offAllNamed(Routes.LOGIN);
  }
}
