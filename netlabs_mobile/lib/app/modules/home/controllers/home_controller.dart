import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import '../../../data/providers/api_provider.dart';
import '../../../routes/app_pages.dart';

class HomeController extends GetxController {
  final storage = GetStorage();
  final ApiProvider _api = Get.find<ApiProvider>();

  var studentName = ''.obs;
  var studentClass = ''.obs;

  var totalPertemuan = '0/0'.obs;
  var rataRataNilai = '0'.obs;
  var streakBelajar = '0 Hari'.obs;

  var pertemuanAktif = <Map<String, dynamic>>[].obs;
  var isLoading = false.obs;

  var terakhirTanyaAI = 'Belum ada pertanyaan'.obs;
  var waktuTanyaAI = ''.obs;

  @override
  void onInit() {
    super.onInit();
    studentName.value = storage.read('nama') ?? 'Siswa';
    studentClass.value = storage.read('kelas') ?? '-';
    loadStatistik();
    loadPertemuanAktif();
    loadRiwayatChat();
  }

  void loadStatistik() async {
    try {
      final response = await _api.getStatistikSiswa();
      final s = response.data['data']['statistik'];
      totalPertemuan.value =
          '${s['total_pertemuan_selesai']}/${s['total_pertemuan']}';
      rataRataNilai.value = '${s['rata_rata_nilai']}';
    } catch (e) {
      print('Gagal memuat statistik: $e');
    }
  }

  void loadPertemuanAktif() async {
    try {
      final response = await _api.getPertemuanAktif();
      final list = response.data['data'] as List;
      pertemuanAktif.value =
          list.map((e) => Map<String, dynamic>.from(e)).toList();
    } catch (e) {
      print('Gagal memuat pertemuan aktif: $e');
    }
  }

  void loadRiwayatChat() async {
    try {
      final response = await _api.getRiwayatChat();
      final list = response.data['data'] as List;
      if (list.isNotEmpty) {
        final last = list.last;
        if (last['sender'] == 'siswa') {
          terakhirTanyaAI.value = last['pesan'];
          waktuTanyaAI.value = last['waktu'];
        }
      }
    } catch (e) {
      print('Gagal memuat riwayat chat: $e');
    }
  }

  void logout() {
    _api.logout().catchError((_) {});
    storage.remove('token');
    storage.remove('nama');
    storage.remove('kelas');
    storage.remove('role');
    Get.offAllNamed(Routes.LOGIN);
  }
}
