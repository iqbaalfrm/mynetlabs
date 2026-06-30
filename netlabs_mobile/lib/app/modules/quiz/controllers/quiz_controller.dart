import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import '../../../data/providers/api_provider.dart';

class QuizController extends GetxController {
  final ApiProvider _api = Get.find<ApiProvider>();
  final _storage = GetStorage();

  var daftarSoal = <Map<String, dynamic>>[].obs;
  var currentQuestionIndex = 0.obs;
  var selectedJawaban = ''.obs;
  var isQuizFinished = false.obs;
  var nilaiAkhir = 0.0.obs;
  var rekomendasiAi = ''.obs;
  var isLoading = false.obs;

  late int pertemuanId;

  int get jumlahBenar => _jumlahBenar.value;
  final _jumlahBenar = 0.obs;

  // Menampung jawaban siswa per soal (soal_id -> 'A'/'B'/'C'/'D')
  final Map<int, String> _jawabanSiswa = {};

  String get _cacheKey => 'quiz_progress_$pertemuanId';

  @override
  void onInit() {
    super.onInit();
    pertemuanId = Get.arguments['pertemuan_id'] ?? 0;
    _restoreProgress();
    loadSoal();
  }

  void _restoreProgress() {
    final saved = _storage.read<List>(_cacheKey);
    if (saved != null) {
      for (final item in saved) {
        final m = Map<String, dynamic>.from(item as Map);
        _jawabanSiswa[m['soal_id'] as int] = m['jawaban'] as String;
      }
      // Restore selectedJawaban for current question if soal already loaded
      _syncSelectedJawaban();
    }
  }

  void _syncSelectedJawaban() {
    if (daftarSoal.isEmpty) return;
    final soal = daftarSoal[currentQuestionIndex.value];
    selectedJawaban.value = _jawabanSiswa[soal['id'] as int] ?? '';
  }

  void _saveProgress() {
    final data = _jawabanSiswa.entries
        .map((e) => {'soal_id': e.key, 'jawaban': e.value})
        .toList();
    _storage.write(_cacheKey, data);
  }

  void loadSoal() async {
    isLoading.value = true;
    try {
      final response = await _api.getSoalKuis(pertemuanId);
      final soalList = (response.data['data']['soal'] as List)
          .map((e) => Map<String, dynamic>.from(e))
          .toList();
      daftarSoal.value = soalList;
      _syncSelectedJawaban();
    } catch (e) {
      print('Gagal memuat soal: $e');
    } finally {
      isLoading.value = false;
    }
  }

  void selectOption(String key) {
    selectedJawaban.value = key;
    // Persist immediately so answers survive app kill
    if (daftarSoal.isNotEmpty) {
      final soal = daftarSoal[currentQuestionIndex.value];
      _jawabanSiswa[soal['id'] as int] = key;
      _saveProgress();
    }
  }

  @override
  void onClose() {
    if (!isQuizFinished.value) _saveProgress();
    super.onClose();
  }

  void nextQuestion() {
    if (selectedJawaban.value.isEmpty) return;

    // Confirm dialog before submitting the last question
    if (currentQuestionIndex.value >= daftarSoal.length - 1) {
      _showSubmitConfirm();
      return;
    }

    selectedJawaban.value = '';
    currentQuestionIndex.value++;
    _syncSelectedJawaban();
  }

  void _showSubmitConfirm() {
    Get.defaultDialog(
      title: 'Kumpulkan Kuis?',
      middleText: 'Kamu akan mengirimkan seluruh jawaban dan tidak bisa mengubahnya lagi.',
      textConfirm: 'Ya, Kumpulkan',
      textCancel: 'Periksa Lagi',
      confirmTextColor: Colors.white,
      onConfirm: () {
        Get.back();
        selectedJawaban.value = '';
        submitKuis();
      },
      onCancel: () => Get.back(),
    );
  }

  void submitKuis() async {
    try {
      final jawabanList = <Map<String, dynamic>>[];
      _jawabanSiswa.forEach((soalId, jawaban) {
        jawabanList.add({'soal_id': soalId, 'jawaban': jawaban});
      });

      final response = await _api.submitKuis(pertemuanId, jawabanList);
      final data = response.data['data'];
      nilaiAkhir.value = (data['nilai'] as num).toDouble();
      _jumlahBenar.value = data['jumlah_benar'] as int;
      rekomendasiAi.value = data['rekomendasi_ai'] ?? '';
      isQuizFinished.value = true;
      await _storage.remove(_cacheKey);
    } catch (e) {
      print('Gagal submit kuis: $e');
      hitungNilaiAkhirLokal();
    }
  }

  void hitungNilaiAkhirLokal() {
    if (daftarSoal.isEmpty) {
      isQuizFinished.value = true;
      return;
    }
    nilaiAkhir.value = (_jumlahBenar.value / daftarSoal.length) * 100;
    if (nilaiAkhir.value == 100) {
      rekomendasiAi.value =
          'Luar biasa! Anda telah memahami seluruh konsep praktikum dengan sangat baik.';
    } else if (nilaiAkhir.value >= 70) {
      rekomendasiAi.value =
          'Hasil yang baik! Tinjau kembali modul yang belum dikuasai.';
    } else {
      rekomendasiAi.value =
          'Upaya yang bagus! Tinjau ulang materi dan konsultasi ke AI Tutor.';
    }
    isQuizFinished.value = true;
  }
}
