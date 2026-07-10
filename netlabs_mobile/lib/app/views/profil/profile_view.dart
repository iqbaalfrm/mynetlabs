import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../core/theme/app_theme.dart';
import '../../controllers/profile_controller.dart';

class ProfileView extends GetView<ProfileController> {
  const ProfileView({super.key});

  @override
  Widget build(BuildContext context) {
    // Inisialisasi controller jika belum di-inject via binding
    final controller = Get.put(ProfileController());

    return Scaffold(
      backgroundColor: NetlabsTheme.surface,
      body: Obx(() {
        if (controller.isLoading.value) {
          return const Center(child: CircularProgressIndicator(color: NetlabsTheme.primary));
        }
        
        return SingleChildScrollView(
          child: Column(
            children: [
              // ==========================================
              // PREMIUM HEADER - SOLID BLUE COLOR
              // ==========================================
              Container(
                width: double.infinity,
                padding: const EdgeInsets.only(top: 60, bottom: 80),
                decoration: const BoxDecoration(
                  color: NetlabsTheme.primary,
                  borderRadius: BorderRadius.only(
                    bottomLeft: Radius.circular(32),
                    bottomRight: Radius.circular(32),
                  ),
                ),
                child: const Center(
                  child: Text(
                    "PROFIL SISWA",
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 16,
                      fontWeight: FontWeight.w900,
                      letterSpacing: 2.0,
                    ),
                  ),
                ),
              ),

              // Overlapping Avatar Card in natural layout flow to prevent overlaps
              Transform.translate(
                offset: const Offset(0.0, -50.0),
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child: Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: NetlabsTheme.card,
                      borderRadius: BorderRadius.circular(24),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.06),
                          blurRadius: 20,
                          offset: const Offset(0, 10),
                        ),
                      ],
                    ),
                    child: Column(
                      children: [
                        // Glowing Avatar
                        Container(
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            border: Border.all(color: Colors.white, width: 4),
                            boxShadow: [
                              BoxShadow(
                                  color: NetlabsTheme.primary.withAlpha(50),
                                  blurRadius: 16,
                                  offset: const Offset(0, 4),
                                ),
                            ],
                          ),
                          child: Stack(
                            alignment: Alignment.bottomRight,
                            children: [
                              Obx(() => CircleAvatar(
                                radius: 40,
                                backgroundColor: NetlabsTheme.primary,
                                backgroundImage: controller.fotoProfilUrl.value != null 
                                  ? NetworkImage(controller.fotoProfilUrl.value!) 
                                  : null,
                                child: controller.fotoProfilUrl.value == null 
                                  ? const Icon(Icons.person_rounded, size: 45, color: Colors.white)
                                  : null,
                              )),
                              GestureDetector(
                                onTap: () => controller.gantiFotoProfil(),
                                child: Container(
                                  padding: const EdgeInsets.all(6),
                                  decoration: BoxDecoration(
                                    color: NetlabsTheme.dark,
                                    shape: BoxShape.circle,
                                    border: Border.all(color: Colors.white, width: 2),
                                  ),
                                  child: Obx(() => controller.isUploading.value 
                                    ? const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                                    : const Icon(Icons.camera_alt_rounded, size: 14, color: Colors.white)
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 12),
                        
                        // Student Name
                        Obx(() => Text(
                          controller.nama.value.isEmpty ? "Siswa" : controller.nama.value,
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.w800,
                            color: NetlabsTheme.dark,
                          ),
                        )),
                        const SizedBox(height: 4),
                        
                        // Student NIS
                        Obx(() => Text(
                          "NIS: ${controller.nis.value.isEmpty ? '-' : controller.nis.value}",
                          style: const TextStyle(
                            fontSize: 13,
                            color: NetlabsTheme.textSecondary,
                            fontWeight: FontWeight.w500,
                          ),
                        )),
                        const SizedBox(height: 16),
                      ],
                    ),
                  ),
                ),
              ),
              
              // ==========================================
              // DETAIL PROFIL SECTION
              // ==========================================
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      "Detail Profil",
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w800,
                        color: NetlabsTheme.dark,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Container(
                      width: double.infinity,
                      decoration: BoxDecoration(
                        color: NetlabsTheme.card,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: NetlabsTheme.border, width: 0.5),
                        boxShadow: NetlabsTheme.shadowSm,
                      ),
                      child: Column(
                        children: [
                          Obx(() => _buildProfileDetailRow(
                            Icons.badge_rounded,
                            "Nama Lengkap",
                            controller.nama.value.isEmpty ? '-' : controller.nama.value,
                          )),
                          _buildDivider(),
                          Obx(() => _buildProfileDetailRow(
                            Icons.numbers_rounded,
                            "NIS",
                            controller.nis.value.isEmpty ? '-' : controller.nis.value,
                          )),
                          _buildDivider(),
                          Obx(() => _buildProfileDetailRow(
                            Icons.class_rounded,
                            "Kelas",
                            controller.kelas.value.isEmpty ? '-' : controller.kelas.value,
                          )),
                          _buildDivider(),
                          Obx(() => _buildProfileDetailRow(
                            Icons.school_rounded,
                            "Sekolah",
                            controller.sekolah.value.isEmpty ? '-' : controller.sekolah.value,
                          )),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),
                  ],
                ),
              ),

              // ==========================================
              // STATISTICS SECTION
              // ==========================================
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      "Statistik Belajar",
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w800,
                        color: NetlabsTheme.dark,
                      ),
                    ),
                    const SizedBox(height: 12),
                    
                    // Row for modules and scores
                    Row(
                      children: [
                        Obx(() => _buildStatBox(
                          "Progress Modul",
                          "${controller.totalPertemuanSelesai.value}/${controller.totalPertemuan.value}",
                          "Modul Praktikum",
                          Icons.menu_book_rounded,
                          NetlabsTheme.primary, // Blue
                        )),
                        const SizedBox(width: 12),
                        Obx(() => _buildStatBox(
                          "Rata-rata Nilai",
                          "${controller.rataRataNilai.value}",
                          "Skor Evaluasi",
                          Icons.analytics_rounded,
                          NetlabsTheme.success, // Emerald Green
                        )),
                      ],
                    ),
                    const SizedBox(height: 12),
                    
                    // AI Interaction Card
                    Obx(() => _buildLongStatBox(
                      "Interaksi AI Chat Tutor",
                      "${controller.totalChatKeAI.value} Pertanyaan Praktikum",
                      "Telah dijawab oleh AI",
                      Icons.auto_awesome_rounded,
                      NetlabsTheme.accent, // Purple
                    )),
                    
                    const SizedBox(height: 28),
                    
                    // ==========================================
                    // SYSTEM ACTION SECTION
                    // ==========================================
                    Container(
                      decoration: BoxDecoration(
                        color: NetlabsTheme.card,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: NetlabsTheme.border, width: 0.5),
                      ),
                      child: Column(
                        children: [
                          _buildActionTile(
                            Icons.lock_outline,
                            "Ganti Password",
                            () => controller.gantiPassword(),
                          ),
                          const Divider(height: 1, indent: 56, color: NetlabsTheme.surface),
                          _buildActionTile(
                            Icons.shield_outlined,
                            "Kebijakan Privasi",
                            () {},
                          ),
                          const Divider(height: 1, indent: 56, color: NetlabsTheme.surface),
                          _buildActionTile(
                            Icons.info_outline_rounded,
                            "Tentang Aplikasi Netlabs",
                            () {},
                          ),
                        ],
                      ),
                    ),
                    
                    const SizedBox(height: 24),
                    
                    // Logout button
                    ElevatedButton(
                      onPressed: () => _showLogoutBottomSheet(),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: NetlabsTheme.card,
                        foregroundColor: NetlabsTheme.danger,
                        minimumSize: const Size(double.infinity, 52),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                          side: BorderSide(color: NetlabsTheme.danger.withAlpha(50), width: 0.5),
                        ),
                        elevation: 0,
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.logout_rounded, size: 20, color: NetlabsTheme.danger),
                          const SizedBox(width: 8),
                          const Text(
                            "Keluar dari Aplikasi",
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                    
                    const SizedBox(height: 40),
                  ],
                ),
              ),
            ],
          ),
        );
      }),
    );
  }


  // Beautiful Grid Stat Card
  Widget _buildStatBox(String title, String value, String subtitle, IconData icon, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: NetlabsTheme.card,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: NetlabsTheme.border, width: 0.5),
          boxShadow: NetlabsTheme.shadowSm,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            CircleAvatar(
              radius: 18,
              backgroundColor: color.withOpacity(0.1),
              child: Icon(icon, color: color, size: 18),
            ),
            const SizedBox(height: 14),
            Text(
              value,
              style: const TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.w900,
                color: NetlabsTheme.dark,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              title,
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.bold,
                color: NetlabsTheme.textSecondary,
              ),
            ),
            Text(
              subtitle,
              style: const TextStyle(
                fontSize: 10,
                color: NetlabsTheme.textMuted,
              ),
            ),
          ],
        ),
      ),
    );
  }

  // Beautiful Long Row Stat Card
  Widget _buildLongStatBox(String title, String value, String subtitle, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: NetlabsTheme.card,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: NetlabsTheme.border, width: 0.5),
        boxShadow: NetlabsTheme.shadowSm,
      ),
      child: Row(
        children: [
          CircleAvatar(
            radius: 22,
            backgroundColor: color.withOpacity(0.1),
            child: Icon(icon, color: color, size: 22),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: NetlabsTheme.dark,
                  ),
                ),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                    color: NetlabsTheme.textSecondary,
                  ),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              subtitle,
              style: TextStyle(
                fontSize: 9,
                color: color,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // Profile Detail Row
  Widget _buildProfileDetailRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: NetlabsTheme.primary.withOpacity(0.08),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: NetlabsTheme.primary, size: 18),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: NetlabsTheme.textMuted,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: NetlabsTheme.dark,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // Divider for profile detail
  Widget _buildDivider() {
    return const Divider(height: 1, indent: 56, endIndent: 16, color: Color(0xFFF0F0F5));
  }

  // Action Tile
  Widget _buildActionTile(IconData icon, String title, VoidCallback onTap) {
    return ListTile(
      leading: Icon(icon, color: NetlabsTheme.textSecondary, size: 22),
      title: Text(
        title,
        style: const TextStyle(
          fontSize: 14,
          fontWeight: FontWeight.bold,
          color: NetlabsTheme.textPrimary,
        ),
      ),
      trailing: const Icon(Icons.arrow_forward_ios_rounded, size: 14, color: Colors.grey),
      onTap: onTap,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
    );
  }

  // Bottom Sheet Logout
  void _showLogoutBottomSheet() {
    Get.bottomSheet(
      Container(
        padding: const EdgeInsets.all(24),
        decoration: const BoxDecoration(
          color: NetlabsTheme.card,
          borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: NetlabsTheme.border,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            const SizedBox(height: 20),
            const Text(
              "Konfirmasi Keluar",
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w900,
                color: NetlabsTheme.danger,
              ),
            ),
            const SizedBox(height: 12),
            const Text(
              "Apakah kamu yakin ingin keluar dari akun Netlabs siswa?",
              textAlign: TextAlign.center,
              style: TextStyle(
                color: NetlabsTheme.textSecondary,
                fontSize: 14,
              ),
            ),
            const SizedBox(height: 24),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Get.back(),
                    style: OutlinedButton.styleFrom(
                      minimumSize: const Size(double.infinity, 48),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                      side: const BorderSide(color: NetlabsTheme.border),
                    ),
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
                    style: ElevatedButton.styleFrom(
                      backgroundColor: NetlabsTheme.danger,
                      minimumSize: const Size(double.infinity, 48),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                      elevation: 0,
                    ),
                    child: const Text(
                      "Ya, Keluar",
                      style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                    ),
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
