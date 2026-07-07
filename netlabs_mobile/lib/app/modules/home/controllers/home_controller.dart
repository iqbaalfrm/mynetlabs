import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import '../../../data/providers/api_provider.dart';
import '../../../data/services/auth_service.dart';
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
  final _auth = Get.find<AuthService>();
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
    if (!_auth.isLoggedIn) { Get.offAllNamed(Routes.LOGIN); return; }
    studentName.value = GetStorage().read('nama') ?? 'Siswa';
    studentClass.value = GetStorage().read('kelas') ?? '-';
    updateGreeting();
    loadDashboard();
    _checkPasswordDefault();
  }

  void _checkPasswordDefault() {
    final box = GetStorage();
    final isDefault = box.read('password_is_default') ?? false;
    if (isDefault == true) {
      Future.delayed(const Duration(milliseconds: 800), () => Get.toNamed(Routes.CHANGE_PASSWORD));
    }
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
    } catch (e) {
      isError.value = true;
      _setErrorMessage(e);
    } finally {
      isLoading.value = false;
    }
  }

  void _setErrorMessage(dynamic e) {
    if (e is DioException) {
      switch (e.type) {
        case DioExceptionType.connectionTimeout:
        case DioExceptionType.receiveTimeout:
          errorMessage.value = 'Koneksi timeout. Periksa jaringan Anda.';
          break;
        case DioExceptionType.connectionError:
          errorMessage.value = 'Tidak dapat terhubung ke server. Periksa koneksi internet.';
          break;
        default:
          errorMessage.value = 'Gagal memuat. Tarik ke bawah untuk muat ulang.';
      }
    } else {
      errorMessage.value = 'Gagal memuat. Tarik ke bawah untuk muat ulang.';
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
      List<Map<String, dynamic>> s1 = [];
      List<Map<String, dynamic>> s2 = [];
      if (d is Map) {
        if (d['1'] != null) {
          s1 = (d['1'] as List).map((e) => Map<String, dynamic>.from(e)).toList();
        }
        if (d['2'] != null) {
          s2 = (d['2'] as List).map((e) => Map<String, dynamic>.from(e)).toList();
        }
      } else if (d is List) {
        final list = d.map((e) => Map<String, dynamic>.from(e)).toList();
        s1 = list.where((e) => e['semester'].toString() == '1').toList();
        s2 = list.where((e) => e['semester'].toString() == '2').toList();
      }
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

  void bukaPertemuan(PertemuanCard card) => Get.toNamed('/detail-materi', arguments: {'id': card.id, 'nomor': card.nomor, 'judul': card.judul});
  void bukaMateri() => Get.toNamed('/materi');
  void bukaChatbot() => Get.toNamed('/chatbot');
  void bukaQuiz(int pertemuanId) => Get.toNamed('/quiz', arguments: {'pertemuan_id': pertemuanId});

  Future<void> logout() async {
    try { await _api.logout(); } catch (_) {}
    await _auth.clearSession();
    Get.offAllNamed(Routes.LOGIN);
  }
}
