import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/profile_controller.dart';

class ProfileView extends GetView<ProfileController> {
  const ProfileView({super.key});

  @override
  Widget build(BuildContext context) {
    // Inisialisasi controller jika belum di-inject via binding
    final controller = Get.put(ProfileController());

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text(
          "Profil Siswa",
          style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1E3A8A)),
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20.0),
        child: Column(
          children: [
            // ==========================================
            // KARTU AVATAR & IDENTITAS UTAMA[cite: 1]
            // ==========================================
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: Colors.grey.withAlpha(30)),
              ),
              child: Column(
                children: [
                  const CircleAvatar(
                    radius: 45,
                    backgroundColor: Color(0xFF3B82F6),
                    child: Icon(Icons.person_rounded, size: 50, color: Colors.white),
                  ),
                  const SizedBox(height: 16),
                  Obx(() => Text(
                        controller.nama.value,
                        textAlign: double.tryParse('center') != null ? TextAlign.center : TextAlign.center,
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E3A8A)),
                      )),
                  const SizedBox(height: 4),
                  Obx(() => Text(
                        "NIS: ${controller.nis.value}",
                        style: const TextStyle(fontSize: 14, color: Colors.grey),
                      )),
                  const SizedBox(height: 12),
                  const Divider(),
                  const SizedBox(height: 8),
                  
                  // Info Kelas & Sekolah[cite: 1]
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      _buildMetaInfo(Icons.class_rounded, "Kelas", controller.kelas.value),
                      _buildMetaInfo(Icons.school_rounded, "Sekolah", controller.sekolah.value),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // ==========================================
            // AKADEMIK STATISTIK CARDS[cite: 1]
            // ==========================================
            const Align(
              alignment: Alignment.centerLeft,
              child: Text(
                "Statistik Belajar",
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF1E3A8A)),
              ),
            ),
            const SizedBox(height: 12),
            
            Row(
              children: [
                Obx(() => _buildStatBox(
                      "Progres Modul",
                      "${controller.totalPertemuanSelesai.value}/${controller.totalPertemuan.value}",
                      Icons.menu_book_rounded,
                      Colors.blue,
                    )),
                const SizedBox(width: 12),
                Obx(() => _buildStatBox(
                      "Rata-rata Nilai",
                      "${controller.rataRataNilai.value}",
                      Icons.analytics_rounded,
                      Colors.green,
                    )),
              ],
            ),
            const SizedBox(height: 12),
            
            Obx(() => _buildLongStatBox(
                  "Interaksi AI Tutor",
                  "${controller.totalChatKeAI.value} Pertanyaan Berhasil Dijawab",
                  Icons.psychology_rounded,
                  Colors.purple,
                )),
            const SizedBox(height: 30),

            // ==========================================
            // TOMBOL LOGOUT[cite: 1]
            // ==========================================
            ElevatedButton.icon(
              onPressed: () => _showLogoutBottomSheet(),
              icon: const Icon(Icons.logout_rounded, color: Colors.white),
              label: const Text("Keluar dari Aplikasi", style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.redAccent,
                minimumSize: const Size(double.infinity, 50),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // Helper Widget: Meta Info Identitas[cite: 1]
  Widget _buildMetaInfo(IconData icon, String label, String value) {
    return Column(
      children: [
        Icon(icon, color: const Color(0xFF3B82F6), size: 20),
        const SizedBox(height: 4),
        Text(label, style: const TextStyle(fontSize: 11, color: Colors.grey)),
        Text(value, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF1E3A8A))),
      ],
    );
  }

  // Helper Widget: Kotak Statistik Grid[cite: 1]
  Widget _buildStatBox(String title, String value, IconData icon, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.grey.withAlpha(30)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icon, color: color, size: 24),
            const SizedBox(height: 12),
            Text(value, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.black87)),
            Text(title, style: const TextStyle(fontSize: 12, color: Colors.grey)),
          ],
        ),
      ),
    );
  }

  // Helper Widget: Kotak Statistik Baris Panjang[cite: 1]
  Widget _buildLongStatBox(String title, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.withAlpha(30)),
      ),
      child: Row(
        children: [
          CircleAvatar(
            backgroundColor: color.withAlpha(25),
            child: Icon(icon, color: color),
          ),
          const SizedBox(width: 16),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: const TextStyle(fontSize: 12, color: Colors.grey)),
              Text(value, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.black87)),
            ],
          ),
        ],
      ),
    );
  }

  // Bottom Sheet Konfirmasi Logout[cite: 1]
  void _showLogoutBottomSheet() {
    Get.bottomSheet(
      Container(
        padding: const EdgeInsets.all(20),
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text("Konfirmasi Keluar", style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.redAccent)),
            const SizedBox(height: 12),
            const Text("Apakah kamu yakin ingin keluar dari akun Netlabs siswa?", textAlign: TextAlign.center),
            const SizedBox(height: 24),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Get.back(),
                    child: const Text("Batal"),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: () {
                      Get.back();
                      controller.logout();
                    },
                    style: ElevatedButton.styleFrom(backgroundColor: Colors.redAccent),
                    child: const Text("Ya, Keluar", style: TextStyle(color: Colors.white)),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}