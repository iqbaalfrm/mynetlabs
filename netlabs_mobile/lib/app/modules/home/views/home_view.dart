import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/home_controller.dart';

class HomeView extends GetView<HomeController> {
  const HomeView({super.key});

  // Warna tema Netlabs
  static const Color _primary = Color(0xFF0D9488);
  static const Color _dark = Color(0xFF0F766E);
  static const Color _bg = Color(0xFFF8FAFC);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _bg,
      body: SafeArea(
        child: Obx(() {
          if (controller.isLoading.value) {
            return const Center(child: CircularProgressIndicator(color: _primary));
          }
          return RefreshIndicator(
            onRefresh: () async => controller.loadDashboard(),
            color: _primary,
            child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(20.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildHeader(),
                  const SizedBox(height: 24),
                  _buildProgressRing(),
                  const SizedBox(height: 24),
                  _buildLanjutBelajar(),
                  const SizedBox(height: 28),
                  _buildPertemuanAktif(),
                  const SizedBox(height: 28),
                  _buildKuisPending(),
                ],
              ),
            ),
          );
        }),
      ),
      floatingActionButton: Container(
        decoration: BoxDecoration(
          color: const Color(0xFF0F766E),
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(color: const Color(0xFF7C3AED).withAlpha(80), blurRadius: 12, offset: const Offset(0, 6)),
          ],
        ),
        child: FloatingActionButton.extended(
          onPressed: () => controller.bukaChatbot(),
          backgroundColor: Colors.transparent,
          elevation: 0,
          icon: const Icon(Icons.smart_toy_rounded, color: Colors.white, size: 24),
          label: const Text(
            'AI Tutor',
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
          ),
        ),
      ),
    );
  }

  // ===== SECTION 1: HEADER DENGAN GREETING DINAMIS =====
  Widget _buildHeader() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Expanded(
          child: Row(
            children: [
              // Avatar bulat dengan inisial
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: const Color(0xFF0F766E),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Center(
                  child: Obx(() => Text(
                        _getInitials(controller.studentName.value),
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 18,
                        ),
                      )),
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Obx(() => Text(
                          controller.greeting.value,
                          style: const TextStyle(fontSize: 13, color: Colors.grey),
                        )),
                    Obx(() => Text(
                          controller.studentName.value,
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: _dark,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        )),
                    Obx(() => Container(
                          margin: const EdgeInsets.only(top: 2),
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: _primary.withAlpha(20),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            controller.studentClass.value,
                            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: _primary),
                          ),
                        )),
                  ],
                ),
              ),
            ],
          ),
        ),
        IconButton(
          icon: const Icon(Icons.logout_rounded, color: Colors.redAccent),
          onPressed: () => _showLogoutConfirmation(),
        ),
      ],
    );
  }

  // ===== SECTION 2: PROGRESS RING SEMESTER =====
  Widget _buildProgressRing() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: const Color(0xFF0F766E),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(color: _primary.withAlpha(40), blurRadius: 12, offset: const Offset(0, 6)),
        ],
      ),
      child: Row(
        children: [
          // Progress ring melingkar
          SizedBox(
            width: 80,
            height: 80,
            child: Stack(
              children: [
                Obx(() => CircularProgressIndicator(
                      value: controller.progressSemester.value,
                      backgroundColor: Colors.white.withAlpha(40),
                      color: Colors.white,
                      strokeWidth: 7,
                    )),
                Center(
                  child: Obx(() => Text(
                        '${(controller.progressSemester.value * 100).toInt()}%',
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 18,
                        ),
                      )),
                ),
              ],
            ),
          ),
          const SizedBox(width: 20),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Progress Semester',
                  style: TextStyle(color: Colors.white70, fontSize: 13),
                ),
                const SizedBox(height: 4),
                Obx(() => Text(
                      '${controller.totalTopikSelesai.value} dari ${controller.totalTopik.value} topik selesai',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    )),
                const SizedBox(height: 6),
                Obx(() => Text(
                      '${controller.totalPertemuanSelesai.value}/${controller.totalPertemuan.value} pertemuan tuntas',
                      style: const TextStyle(color: Colors.white70, fontSize: 12),
                    )),
              ],
            ),
          ),
        ],
      ),
    );
  }
  // ===== SECTION 4: SHORTCUT LANJUT BELAJAR =====
  Widget _buildLanjutBelajar() {
    return Obx(() {
      final p = controller.lanjutBelajar.value;
      if (p == null) return const SizedBox.shrink();
      double progress = (p['progress'] as num?)?.toDouble() ?? 0.0;
      return GestureDetector(
        onTap: () => controller.bukaPertemuan(p),
        child: Container(
          width: double.infinity,
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: _primary.withAlpha(60), width: 1.5),
            boxShadow: [
              BoxShadow(color: _primary.withAlpha(20), blurRadius: 10, offset: const Offset(0, 4)),
            ],
          ),
          child: Row(
            children: [
              Container(
                width: 50,
                height: 50,
                decoration: BoxDecoration(
                  color: _primary.withAlpha(15),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.play_circle_fill_rounded, color: _primary, size: 30),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Lanjut Belajar', style: TextStyle(fontSize: 12, color: _primary, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 2),
                    Text(
                      'Pertemuan ${p['nomor']} - ${p['judul']}',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: _dark),
                    ),
                    const SizedBox(height: 6),
                    LinearProgressIndicator(
                      value: progress,
                      backgroundColor: Colors.grey.withAlpha(30),
                      color: _primary,
                      minHeight: 5,
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              const Icon(Icons.arrow_forward_ios_rounded, size: 16, color: _primary),
            ],
          ),
        ),
      );
    });
  }

  // ===== SECTION 5: PERTEMUAN AKTIF (BERWARNA TEMA) =====
  Widget _buildPertemuanAktif() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Pertemuan Aktif',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: _dark),
            ),
            TextButton(
              onPressed: () => controller.bukaMateri(),
              child: const Text('Lihat Semua', style: TextStyle(fontSize: 13, color: _primary, fontWeight: FontWeight.w600)),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Obx(() {
          if (controller.pertemuanAktif.isEmpty) {
            return _buildEmptyState('Belum ada pertemuan yang sedang berjalan', Icons.book_outlined);
          }
          return SizedBox(
            height: 150,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: controller.pertemuanAktif.length,
              separatorBuilder: (_, __) => const SizedBox(width: 14),
              itemBuilder: (context, index) {
                final p = controller.pertemuanAktif[index];
                return _buildPertemuanCard(p);
              },
            ),
          );
        }),
      ],
    );
  }

  Widget _buildPertemuanCard(Map<String, dynamic> p) {
    double progress = (p['progress'] as num?)?.toDouble() ?? 0.0;
    int nomor = (p['nomor'] as num?)?.toInt() ?? 0;
    String judul = p['judul'] ?? '';
    String topikInfo = p['topik'] ?? '-';

    return GestureDetector(
      onTap: () => controller.bukaPertemuan(p),
      child: Container(
        width: 230,
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: const Color(0xFF0F766E),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: _primary.withAlpha(40)),
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
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: _primary,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        'Pertemuan $nomor',
                        style: const TextStyle(fontSize: 10, color: Colors.white, fontWeight: FontWeight.bold),
                      ),
                    ),
                    const Spacer(),
                    Icon(Icons.layers_rounded, size: 12, color: Colors.grey.shade500),
                    const SizedBox(width: 3),
                    Text(topikInfo, style: TextStyle(fontSize: 10, color: Colors.grey.shade600)),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  judul,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: _dark),
                ),
              ],
            ),
            Column(
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text('Progress', style: TextStyle(fontSize: 10, color: Colors.grey.shade600)),
                    Text('${(progress * 100).toInt()}%', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: _primary)),
                  ],
                ),
                const SizedBox(height: 5),
                LinearProgressIndicator(
                  value: progress,
                  backgroundColor: Colors.grey.withAlpha(30),
                  color: _primary,
                  minHeight: 5,
                  borderRadius: BorderRadius.circular(10),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  // ===== SECTION 6: KUIS BELUM DIKERJAKAN =====
  Widget _buildKuisPending() {
    return Obx(() {
      if (controller.kuisBelumDikerjakan.isEmpty) return const SizedBox.shrink();
      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.assignment_late_rounded, size: 18, color: Colors.orange),
              const SizedBox(width: 6),
              const Text(
                'Kuis Siap Dikerjakan',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: _dark),
              ),
              const Spacer(),
              Text(
                '${controller.kuisBelumDikerjakan.length}',
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.white, backgroundColor: Colors.orange),
              ),
            ],
          ),
          const SizedBox(height: 12),
          ...controller.kuisBelumDikerjakan.map((p) => _buildKuisCard(p)),
        ],
      );
    });
  }

  Widget _buildKuisCard(Map<String, dynamic> p) {
    int nomor = (p['nomor'] as num?)?.toInt() ?? 0;
    String judul = p['judul'] ?? '';
    int pertemuanId = (p['id'] as num?)?.toInt() ?? 0;

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.orange.withAlpha(8),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.orange.withAlpha(50)),
      ),
      child: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: Colors.orange.withAlpha(20),
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(Icons.quiz_rounded, color: Colors.orange, size: 22),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Pertemuan $nomor', style: const TextStyle(fontSize: 10, color: Colors.orange, fontWeight: FontWeight.bold)),
                Text(
                  judul,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: _dark),
                ),
              ],
            ),
          ),
          ElevatedButton(
            onPressed: () => controller.bukaQuiz(pertemuanId),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.orange,
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            child: const Text('Kerjakan', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.white)),
          ),
        ],
      ),
    );
  }


  // ===== HELPER WIDGETS =====
  Widget _buildEmptyState(String text, IconData icon) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.grey.withAlpha(30)),
      ),
      child: Column(
        children: [
          Icon(icon, size: 36, color: Colors.grey.shade400),
          const SizedBox(height: 8),
          Text(text, style: TextStyle(fontSize: 13, color: Colors.grey.shade500), textAlign: TextAlign.center),
        ],
      ),
    );
  }

  String _getInitials(String name) {
    if (name.isEmpty) return '?';
    final parts = name.trim().split(' ');
    if (parts.length >= 2) {
      return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
    }
    return name[0].toUpperCase();
  }

  // ===== LOGOUT CONFIRMATION =====
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
            const Text('Keluar Akun', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.red)),
            const SizedBox(height: 10),
            const Text('Apakah kamu yakin ingin keluar dari aplikasi Netlabs?', textAlign: TextAlign.center),
            const SizedBox(height: 20),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Get.back(),
                    child: const Text('Batal'),
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
                    child: const Text('Ya, Keluar', style: TextStyle(color: Colors.white)),
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
