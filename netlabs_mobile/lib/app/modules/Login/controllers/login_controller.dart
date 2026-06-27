import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import '../../../routes/app_pages.dart';

class LoginController extends GetxController {
  // Global Key untuk validasi Form di UI
  final loginFormKey = GlobalKey<FormState>();
  final storage = GetStorage();

  // Controller untuk menangkap teks input siswa
  late TextEditingController nisController;
  late TextEditingController passwordController;

  // State reaktif (Obs) untuk status Loading dan Visibility Password
  var isLoading = false.obs;
  var isPasswordObscured = true.obs;

  @override
  void onInit() {
    super.onInit();
    nisController = TextEditingController();
    passwordController = TextEditingController();
  }

  @override
  void onClose() {
    nisController.dispose();
    passwordController.dispose();
    super.onClose();
  }

  // Fungsi membalikkan status sembunyikan/tampilkan password
  void togglePasswordVisibility() {
    isPasswordObscured.value = !isPasswordObscured.value;
  }

  // Fungsi eksekusi Login
  void login() async {
    // 1. Validasi teks input (apakah kosong, dsb)
    if (loginFormKey.currentState!.validate()) {
      isLoading.value = true;

      try {
        // 2. Simulasi Request ke Laravel (Nanti diganti dengan Dio client asli)
        await Future.delayed(const Duration(seconds: 2));

        // Anggap backend Laravel mengembalikan Token JWT sukses
        String dummyToken = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9";
        
        // 3. Simpan token ke memori lokal HP biar ga perlu login ulang
        await storage.write('token', dummyToken);

        isLoading.value = false;
        
        Get.snackbar(
          'Sukses',
          'Selamat datang di Netlabs!',
          snackPosition: SnackPosition.BOTTOM,
          backgroundColor: Colors.green,
          colorText: Colors.white,
        );

        // 4. Tendang siswa ke halaman Beranda (Home) dan bersihkan tumpukan page login
        Get.offAllNamed(Routes.HOME);

      } catch (e) {
        isLoading.value = false;
        Get.snackbar(
          'Gagal Login',
          'NIS atau Kata Sandi salah.',
          snackPosition: SnackPosition.BOTTOM,
          backgroundColor: Colors.red,
          colorText: Colors.white,
        );
      }
    }
  }
}