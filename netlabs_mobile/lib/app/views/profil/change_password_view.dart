import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../core/theme/app_theme.dart';
import '../../controllers/change_password_controller.dart';

class ChangePasswordView extends GetView<ChangePasswordController> {
  const ChangePasswordView({super.key});

  @override
  Widget build(BuildContext context) {
    final c = Get.put(ChangePasswordController());

    return Scaffold(
      backgroundColor: NetlabsTheme.surface,
      appBar: AppBar(
        title: const Text('Ganti Password', style: TextStyle(fontWeight: FontWeight.w700)),
        centerTitle: true,
        backgroundColor: NetlabsTheme.primary,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Info banner
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.orange.shade50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.orange.shade200),
              ),
              child: const Row(
                children: [
                  Icon(Icons.info_outline, color: Colors.orange, size: 22),
                  SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      'Demi keamanan akun, gunakan password yang kuat dan tidak mudah ditebak.',
                      style: TextStyle(fontSize: 13, color: Colors.black87),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Password Lama
            const Text('Password Lama', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: NetlabsTheme.dark)),
            const SizedBox(height: 8),
            TextField(
              controller: c.pwLamaCtrl,
              obscureText: true,
              decoration: InputDecoration(
                hintText: 'Masukkan password lama',
                prefixIcon: const Icon(Icons.lock_outline),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                filled: true,
                fillColor: Colors.white,
              ),
            ),
            const SizedBox(height: 20),

            // Password Baru
            const Text('Password Baru', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: NetlabsTheme.dark)),
            const SizedBox(height: 8),
            TextField(
              controller: c.pwBaruCtrl,
              obscureText: true,
              decoration: InputDecoration(
                hintText: 'Minimal 6 karakter',
                prefixIcon: const Icon(Icons.lock_open),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                filled: true,
                fillColor: Colors.white,
              ),
            ),
            const SizedBox(height: 20),

            // Konfirmasi Password Baru
            const Text('Konfirmasi Password Baru', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: NetlabsTheme.dark)),
            const SizedBox(height: 8),
            TextField(
              controller: c.pwKonfirmCtrl,
              obscureText: true,
              decoration: InputDecoration(
                hintText: 'Ulangi password baru',
                prefixIcon: const Icon(Icons.lock_open),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                filled: true,
                fillColor: Colors.white,
              ),
            ),
            const SizedBox(height: 8),

            // Error message
            Obx(() {
              if (c.errorMsg.isEmpty) return const SizedBox.shrink();
              return Padding(
                padding: const EdgeInsets.only(top: 4),
                child: Text(c.errorMsg.value, style: const TextStyle(color: Colors.red, fontSize: 13)),
              );
            }),
            const SizedBox(height: 32),

            // Submit button
            Obx(() => ElevatedButton(
              onPressed: c.isLoading.value ? null : () => c.submit(),
              style: ElevatedButton.styleFrom(
                backgroundColor: NetlabsTheme.primary,
                foregroundColor: Colors.white,
                minimumSize: const Size(double.infinity, 52),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                elevation: 0,
              ),
              child: c.isLoading.value
                  ? const SizedBox(width: 24, height: 24, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                  : const Text('Simpan Password Baru', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
            )),
          ],
        ),
      ),
    );
  }
}