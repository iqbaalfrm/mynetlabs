import 'package:get/get.dart';
import '../../../data/providers/api_provider.dart';

class QuizController extends GetxController {
  final ApiProvider _api = Get.find<ApiProvider>();

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

  @override
  void onInit() {
    super.onInit();
    pertemuanId = Get.arguments['pertemuan_id'] ?? 0;
    loadSoal();
  }

  void loadSoal() async {
    isLoading.value = true;
    try {
      final response = await _api.getSoalKuis(pertemuanId);
      final soalList = (response.data['data']['soal'] as List)
          .map((e) => Map<String, dynamic>.from(e))
          .toList();
      daftarSoal.value = soalList;
    } catch (e) {
      print('Gagal memuat soal: $e');
    } finally {
      isLoading.value = false;
    }
  }

  void selectOption(String key) {
    selectedJawaban.value = key;
  }

  void nextQuestion() {
    if (selectedJawaban.value.isEmpty) return;

    var soalSekarang = daftarSoal[currentQuestionIndex.value];
    _jawabanSiswa[soalSekarang['id'] as int] = selectedJawaban.value;

    selectedJawaban.value = '';

    if (currentQuestionIndex.value < daftarSoal.length - 1) {
      currentQuestionIndex.value++;
    } else {
      submitKuis();
    }
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
