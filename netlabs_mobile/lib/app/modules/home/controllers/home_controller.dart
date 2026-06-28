import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import '../../../data/providers/api_provider.dart';
import '../../../routes/app_pages.dart';

enum AiStatus { pending, processing, success, failed }

class PertemuanCard {
  final int id, nomor;
  final String judul;
  final double progress;
  final AiStatus aiStatus;
  final bool adaKuis;
  const PertemuanCard({required this.id, required this.nomor, required this.judul, required this.progress, required this.aiStatus, this.adaKuis = false});
}

class HomeController extends GetxController {
  final storage = GetStorage();
  final ApiProvider _api = Get.find<ApiProvider>();
  var studentName = ''.obs, studentClass = ''.obs, greeting = 'Selamat Belajar'.obs;
  var fotoProfilUrl = RxnString();
  var totalPertemuanSelesai = 0.obs, totalPertemuan = 0.obs, totalTopikSelesai = 0.obs, totalTopik = 0.obs, totalChatAI = 0.obs;
  var rataRataNilai = 0.0.obs, progressSemester = 0.0.obs;
  var pertemuanAktif = <Map<String, dynamic>>[].obs, semuaPertemuan = <Map<String, dynamic>>[].obs, kuisBelumDikerjakan = <Map<String, dynamic>>[].obs;
  final lanjutBelajar = Rxn<Map<String, dynamic>>();
  var bentoCards = <PertemuanCard>[].obs;
  var isLoading = false.obs, isError = false.obs;
  var errorMessage = ''.obs;

  @override
  void onInit() {
    super.onInit();
    if (storage.read('token') == null) { Get.offAllNamed(Routes.LOGIN); return; }
    studentName.value = storage.read('nama') ?? 'Siswa';
    studentClass.value = storage.read('kelas') ?? '-';
    updateGreeting();
    loadDashboard();
  }

  void updateGreeting() {
    final h = DateTime.now().hour;
    greeting.value = h < 11 ? 'Selamat Pagi' : h < 15 ? 'Selamat Siang' : h < 19 ? 'Selamat Sore' : 'Selamat Malam';
  }

  Future<void> loadDashboard() async {
    isLoading.value = true; isError.value = false;
    try {
      await Future.wait([loadStatistik(), loadPertemuan()]);
      _buildBentoCards();
    } catch (_) {
      isError.value = true;
      errorMessage.value = 'Gagal memuat. Tarik ke bawah untuk muat ulang.';
      _buildDummyBentoCards();
    } finally {
      isLoading.value = false;
    }
  }

  Future<void> loadStatistik() async {
    try {
      final r = await _api.getStatistikSiswa();
      final p = r.data['data']['profil'];
      if (p != null) fotoProfilUrl.value = p['foto_profil_url'];
      
      final s = r.data['data']['statistik'];
      totalPertemuanSelesai.value = s['total_pertemuan_selesai'] ?? 0;
      totalPertemuan.value = s['total_pertemuan'] ?? 0;
      totalTopikSelesai.value = s['total_topik_selesai'] ?? 0;
      totalTopik.value = s['total_topik'] ?? 0;
      rataRataNilai.value = (s['rata_rata_nilai'] ?? 0).toDouble();
      totalChatAI.value = s['total_chat_ai'] ?? 0;
      if (totalTopik.value > 0) progressSemester.value = totalTopikSelesai.value / totalTopik.value;
    } catch (_) {}
  }

  Future<void> loadPertemuan() async {
    try {
      final r = await _api.getPertemuan();
      final d = r.data['data'];
      final s1 = (d['1'] as List).map((e) => Map<String, dynamic>.from(e)).toList();
      final s2 = (d['2'] as List).map((e) => Map<String, dynamic>.from(e)).toList();
      semuaPertemuan.value = [...s1, ...s2];
      pertemuanAktif.value = semuaPertemuan.where((p) => (p['progress'] as num) > 0 && (p['progress'] as num) < 1).toList();
      if (pertemuanAktif.isNotEmpty) {
        lanjutBelajar.value = Map.from(pertemuanAktif.first);
      } else {
        final blm = semuaPertemuan.where((p) => (p['progress'] as num) == 0).toList();
        if (blm.isNotEmpty) lanjutBelajar.value = Map.from(blm.first);
      }
      kuisBelumDikerjakan.value = semuaPertemuan.where((p) => (p['progress'] as num) >= 1.0).toList();
    } catch (_) {}
  }

  void _buildBentoCards() {
    if (semuaPertemuan.isEmpty) { _buildDummyBentoCards(); return; }
    
    // Tampilkan maksimal 4 modul saja di Dashboard
    bentoCards.value = semuaPertemuan.take(4).map((p) {
      AiStatus st;
      switch (p['status_indexing'] ?? 'pending') {
        case 'success': st = AiStatus.success; break;
        case 'processing': st = AiStatus.processing; break;
        case 'failed': st = AiStatus.failed; break;
        default: st = AiStatus.pending;
      }
      return PertemuanCard(
        id: p['id'], nomor: p['nomor'] ?? p['nomor_urut'] ?? 0,
        judul: p['judul'] ?? 'Tanpa Judul',
        progress: (p['progress'] as num?)?.toDouble() ?? 0.0,
        aiStatus: st, adaKuis: (p['progress'] as num?)?.toDouble() == 1.0,
      );
    }).toList();
  }

  void _buildDummyBentoCards() {
    bentoCards.value = [
      PertemuanCard(id: 1, nomor: 1, judul: 'Pengenalan Jaringan Komputer', progress: 1.0, aiStatus: AiStatus.success, adaKuis: true),
      PertemuanCard(id: 2, nomor: 2, judul: 'Model OSI Layer & TCP/IP', progress: 0.75, aiStatus: AiStatus.success),
      PertemuanCard(id: 3, nomor: 3, judul: 'IP Address & Subnetting', progress: 0.4, aiStatus: AiStatus.processing),
      PertemuanCard(id: 4, nomor: 4, judul: 'Routing Statis & Dinamis', progress: 0.0, aiStatus: AiStatus.pending),
    ];
    totalPertemuan.value = 12; totalPertemuanSelesai.value = 1;
    totalTopik.value = 30; totalTopikSelesai.value = 4;
    rataRataNilai.value = 78.5; totalChatAI.value = 5;
    progressSemester.value = 4 / 30;
  }

  void bukaPertemuan(PertemuanCard card) => Get.toNamed('/detail-materi', arguments: {'id': card.id, 'nomor': card.nomor, 'judul': card.judul});
  void bukaMateri() => Get.toNamed('/materi');
  void bukaChatbot() => Get.toNamed('/chatbot');
  void bukaQuiz(int pertemuanId) => Get.toNamed('/quiz', arguments: {'pertemuan_id': pertemuanId});

  Future<void> logout() async {
    try { await _api.logout(); } catch (_) {}
    storage.remove('token'); storage.remove('nama'); storage.remove('kelas'); storage.remove('role');
    Get.offAllNamed(Routes.LOGIN);
  }
}
