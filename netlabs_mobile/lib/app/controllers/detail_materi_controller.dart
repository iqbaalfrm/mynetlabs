import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:url_launcher/url_launcher.dart';
import '../data/providers/api_provider.dart';
import '../../core/theme/app_theme.dart';
import 'home_controller.dart';
import 'materi_controller.dart';

class DetailMateriController extends GetxController {
  final ApiProvider _api = Get.find<ApiProvider>();

  late int nomorPertemuan;
  late String judulPertemuan;
  late int pertemuanId;

  var deskripsiPertemuan = ''.obs;
  var isiMateri = ''.obs;
  var isKuisEnabled = false.obs;
  var isLoading = false.obs;
  var pdfUrl = ''.obs;

  @override
  void onInit() {
    super.onInit();
    final args = (Get.arguments is Map) ? Map<String, dynamic>.from(Get.arguments) : <String, dynamic>{};
    pertemuanId = args['id'] ?? args['pertemuan_id'] ?? 1;
    nomorPertemuan = args['nomor'] ?? args['nomor_urut'] ?? 1;
    judulPertemuan = args['judul'] ?? 'Detail Pertemuan';
    loadDetail();
  }

  // Memuat data detail pertemuan dari API
  void loadDetail() async {
    isLoading.value = true;
    try {
      final response = await _api.getDetailPertemuan(pertemuanId);
      final d = response.data['data'];
      
      deskripsiPertemuan.value = d['deskripsi'] ?? '';
      pdfUrl.value = d['pdf_url'] ?? '';
      isiMateri.value = d['isi_materi'] ?? '';
      isKuisEnabled.value = d['is_completed'] ?? false;
    } catch (e) {
      debugPrint('Gagal memuat detail: $e');
    } finally {
      isLoading.value = false;
    }
  }

  // Melakukan unduh modul PDF menggunakan browser eksternal
  void unduhPdfMateri() async {
    if (pdfUrl.value.isEmpty) {
      Get.snackbar('Informasi', 'Modul PDF belum diunggah oleh guru untuk pertemuan ini.',
        snackPosition: SnackPosition.BOTTOM,
      );
      return;
    }

    final uri = Uri.parse(pdfUrl.value);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } else {
      Get.snackbar('Error', 'Gagal membuka modul PDF.',
        snackPosition: SnackPosition.BOTTOM,
      );
    }
  }

  // Menandai pertemuan selesai dibaca dan menyimpan progress ke database
  void tandaiSelesai() async {
    if (isKuisEnabled.value) return; // Sudah selesai sebelumnya

    isLoading.value = true;
    try {
      await _api.tandaiPertemuanSelesai(pertemuanId);
      isKuisEnabled.value = true;
      
      // Refresh materi controller dan home controller agar data persentase diperbarui
      if (Get.isRegistered<MateriController>()) {
        Get.find<MateriController>().refreshMateri();
      }
      if (Get.isRegistered<HomeController>()) {
        Get.find<HomeController>().loadStatistik();
        Get.find<HomeController>().loadPertemuan();
      }
      
      Get.snackbar('Sukses', 'Materi berhasil ditandai selesai dibaca!',
        snackPosition: SnackPosition.BOTTOM,
        backgroundColor: NetlabsTheme.success.withAlpha(200),
        colorText: Colors.white,
      );
    } catch (e) {
      debugPrint('Gagal menyimpan progress: $e');
      Get.snackbar('Error', 'Gagal menyimpan progress belajar.',
        snackPosition: SnackPosition.BOTTOM,
      );
    } finally {
      isLoading.value = false;
    }
  }
}
