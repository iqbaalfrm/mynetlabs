import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/home_controller.dart';

class HomeView extends GetView<HomeController> {
  const HomeView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC), // Background abu-abu terang netral
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ==========================================
              // SECTION 1: HEADER & IDENTITAS SISWA
              // ==========================================
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        "Selamat Belajar,",
                        style: TextStyle(fontSize: 14, color: Colors.grey),
                      ),
                      Obx(() => Text(
                            controller.studentName.value,
                            style: const TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF1E3A8A),
                            ),
                          )),
                      Obx(() => Text(
                            controller.studentClass.value,
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w500,
                              color: Color(0xFF3B82F6),
                            ),
                          )),
                    ],
                  ),
                  // Tombol Logout Sementara di Pojok Kanan Atas
                  IconButton(
                    icon: const Icon(Icons.logout_rounded, color: Colors.redAccent),
                    onPressed: () => _showLogoutConfirmation(),
                  )
                ],
              ),
              const SizedBox(height: 24),

              // ==========================================
              // SECTION 2: PROGRESS CARDS (STATISTIK)
              // ==========================================
              Row(
                children: [
                  _buildStatCard("Pertemuan", controller.totalPertemuan.value, Icons.book_rounded, Colors.blue),
                  const SizedBox(width: 12),
                  _buildStatCard("Rata-rata Nilai", controller.rataRataNilai.value, Icons.assignment_turned_in_rounded, Colors.green),
                ],
              ),
              const SizedBox(height: 28),

              // ==========================================
              // SECTION 3: BANNER PERTEMUAN AKTIF
              // ==========================================
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    "Pertemuan Aktif",
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E3A8A)),
                  ),
                  TextButton(
                    onPressed: () {}, // Nanti mengarah ke Tab Materi penuh
                    child: const Text("Lihat semua"),
                  )
                ],
              ),
              const SizedBox(height: 12),
              
              // Horizontal Scroll Cards
              SizedBox(
                height: 140,
                child: Obx(() {
                  if (controller.pertemuanAktif.isEmpty) {
                    return Container(
                      width: double.infinity,
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: Colors.grey.withAlpha(30)),
                      ),
                      child: const Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.event_busy_rounded, color: Colors.grey, size: 36),
                            SizedBox(height: 8),
                            Text(
                              "Tidak ada pertemuan aktif saat ini",
                              style: TextStyle(fontSize: 12, color: Colors.grey, fontWeight: FontWeight.w500),
                            ),
                          ],
                        ),
                      ),
                    );
                  }
                  return ListView.separated(
                    scrollDirection: Axis.horizontal,
                    itemCount: controller.pertemuanAktif.length,
                    separatorBuilder: (context, index) => const SizedBox(width: 14),
                    itemBuilder: (context, index) {
                      var item = controller.pertemuanAktif[index];
                      return _buildActivePertemuanCard(
                        (item['nomor'] as num).toInt(),
                        item['judul'] as String,
                        item['topik'] as String,
                        (item['progress'] as num).toDouble(),
                      );
                    },
                  );
                }),
              ),
              const SizedBox(height: 28),

              // ==========================================
              // SECTION 4: QUICK ACCESS AI TUTOR (RINGKASAN CHAT)
              // ==========================================
              const Text(
                "Aktivitas AI Tutor Terakhir",
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E3A8A)),
              ),
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: Colors.grey.withAlpha(30)),
                  boxShadow: [
                    BoxShadow(color: Colors.grey.withAlpha(15), blurRadius: 10, offset: const Offset(0, 4)),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.psychology_rounded, color: Color(0xFF3B82F6)),
                        const SizedBox(width: 8),
                        const Text("AI Chat Tutor", style: TextStyle(fontWeight: FontWeight.bold)),
                        const Spacer(),
                        Obx(() => Text(
                              controller.waktuTanyaAI.value,
                              style: const TextStyle(fontSize: 11, color: Colors.grey),
                            )),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Obx(() => Text(
                          '"${controller.terakhirTanyaAI.value}"',
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontStyle: FontStyle.italic, color: Colors.black87),
                        )),
                    const SizedBox(height: 14),
                    ElevatedButton.icon(
                      onPressed: () {
                        // Nanti mengarah ke halaman Chat Tutor RAG
                      },
                      icon: const Icon(Icons.chat_bubble_outline_rounded, size: 18, color: Colors.white),
                      label: const Text("Tanya AI Tutor Sekarang", style: TextStyle(color: Colors.white)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF3B82F6),
                        minimumSize: const Size(double.infinity, 40),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                    )
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // Helper Widget: Membuat 3 Kartu Statistik Atas
  Widget _buildStatCard(String title, String value, IconData icon, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 10),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(color: Colors.grey.withAlpha(10), blurRadius: 10, offset: const Offset(0, 4)),
          ],
        ),
        child: Column(
          children: [
            CircleAvatar(
              radius: 18,
              backgroundColor: color.withAlpha(25),
              child: Icon(icon, color: color, size: 18),
            ),
            const SizedBox(height: 10),
            Text(value, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.black87)),
            const SizedBox(height: 2),
            Text(title, textAlign: TextAlign.center, style: const TextStyle(fontSize: 11, color: Colors.grey)),
          ],
        ),
      ),
    );
  }

  // Helper Widget: Membuat Horizontal Scroll Card Pertemuan Aktif
  Widget _buildActivePertemuanCard(int nomor, String judul, String topik, double progress) {
    return Container(
      width: 240,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.withAlpha(30)),
        boxShadow: [
          BoxShadow(color: Colors.grey.withAlpha(15), blurRadius: 8, offset: const Offset(0, 4)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text("Pertemuan $nomor â€” $topik", style: const TextStyle(fontSize: 11, color: Color(0xFF3B82F6), fontWeight: FontWeight.bold)),
              const SizedBox(height: 4),
              Text(
                judul,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.black87),
              ),
            ],
          ),
          Column(
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text("Progress Belajar", style: TextStyle(fontSize: 10, color: Colors.grey)),
                  Text("${(progress * 100).toInt()}%", style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
                ],
              ),
              const SizedBox(height: 4),
              LinearProgressIndicator(
                value: progress,
                backgroundColor: Colors.grey.withAlpha(30),
                color: const Color(0xFF3B82F6),
                minHeight: 5,
                borderRadius: BorderRadius.circular(10),
              )
            ],
          )
        ],
      ),
    );
  }

  // Bottom Sheet Konfirmasi Logout
  void _showLogoutConfirmation() {
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
            const Text("Keluar Akun", style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.red)),
            const SizedBox(height: 10),
            const Text("Apakah kamu yakin ingin keluar dari aplikasi Netlabs?", textAlign: TextAlign.center),
            const SizedBox(height: 20),
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
                    style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
                    child: const Text("Ya, Keluar", style: TextStyle(color: Colors.white)),
                  ),
                ),
              ],
            )
          ],
        ),
      ),
    );
  }
}