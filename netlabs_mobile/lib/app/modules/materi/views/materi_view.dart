import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/materi_controller.dart';

class MateriView extends GetView<MateriController> {
  const MateriView({super.key});

  @override
  Widget build(BuildContext context) {
    // Inisialisasi controller materi
    final controller = Get.put(MateriController());

    return DefaultTabController(
      length: 2, // 2 Tab: Semester 1 dan Semester 2
      child: Scaffold(
        backgroundColor: const Color(0xFFF8FAFC),
        appBar: AppBar(
          title: const Text(
            "Modul Praktikum",
            style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1E3A8A)),
          ),
          backgroundColor: Colors.white,
          elevation: 0,
          bottom: const TabBar(
            labelColor: Color(0xFF3B82F6),
            unselectedLabelColor: Colors.grey,
            indicatorColor: Color(0xFF3B82F6),
            indicatorWeight: 3,
            tabs: [
              Tab(text: "Semester 1"),
              Tab(text: "Semester 2"),
            ],
          ),
        ),
        body: TabBarView(
          children: [
            // Tab Konten Semester 1
            Obx(() => _buildMateriList(controller.materiSemester1)),
            // Tab Konten Semester 2
            Obx(() => _buildMateriList(controller.materiSemester2)),
          ],
        ),
      ),
    );
  }

  // Helper Widget untuk menyusun daftar list kartu materi
  Widget _buildMateriList(List<Map<String, dynamic>> materiList) {
    return ListView.separated(
      padding: const EdgeInsets.all(20),
      itemCount: materiList.length,
      separatorBuilder: (context, index) => const SizedBox(height: 16),
      itemBuilder: (context, index) {
        var item = materiList[index];
        return _buildMateriCard(item);
      },
    );
  }

  // Helper Widget: Kartu Pertemuan Praktikum
  Widget _buildMateriCard(Map<String, dynamic> item) {
    bool isLocked = item['is_locked'] as bool;
    double progress = item['progress'] as double;

    return Container(
      decoration: BoxDecoration(
        color: isLocked ? Colors.grey.shade100 : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.withAlpha(30)),
        boxShadow: [
          if (!isLocked)
            BoxShadow(
              color: Colors.grey.withAlpha(10),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
        ],
      ),
      child: InkWell(
        onTap: isLocked 
            ? () => Get.snackbar(
                "Terkunci", 
                "Selesaikan materi sebelumnya untuk membuka bab ini.", 
                snackPosition: SnackPosition.BOTTOM, 
                backgroundColor: Colors.amber, 
                colorText: Colors.white,
              )
            : () {
                // Navigasi masuk ke Detail Pertemuan dengan membawa argumen dinamis
                Get.toNamed(
                  '/detail-materi',
                  arguments: {
                    'nomor': item['nomor'],
                    'judul': item['judul'],
                  },
                );
              },
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(18.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Bagian Atas: Nomor urut dan indikator kunci
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: isLocked ? Colors.grey.shade300 : const Color(0xFF3B82F6).withAlpha(25),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      "Pertemuan ${item['nomor']}",
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: isLocked ? Colors.grey.shade600 : const Color(0xFF3B82F6),
                      ),
                    ),
                  ),
                  Icon(
                    isLocked ? Icons.lock_rounded : Icons.arrow_forward_ios_rounded,
                    size: 16,
                    color: isLocked ? Colors.grey : const Color(0xFF3B82F6),
                  ),
                ],
              ),
              const SizedBox(height: 12),

              // Judul Pertemuan
              Text(
                item['judul'] as String,
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                  color: isLocked ? Colors.grey.shade600 : const Color(0xFF1E3A8A),
                ),
              ),
              const SizedBox(height: 12),

              // Info Durasi dan Jumlah Topik
              Row(
                children: [
                  Icon(Icons.layers_rounded, size: 14, color: Colors.grey.shade500),
                  const SizedBox(width: 4),
                  Text("${item['topik_count']} Topik", style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                  const SizedBox(width: 16),
                  Icon(Icons.access_time_rounded, size: 14, color: Colors.grey.shade500),
                  const SizedBox(width: 4),
                  Text(item['waktu'] as String, style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                ],
              ),
              const SizedBox(height: 16),

              // Progress Bar Penyelesaian
              if (!isLocked) ...[
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text("Progres Belajar", style: TextStyle(fontSize: 11, color: Colors.grey)),
                    Text("${(progress * 100).toInt()}%", style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                  ],
                ),
                const SizedBox(height: 6),
                LinearProgressIndicator(
                  value: progress,
                  backgroundColor: Colors.grey.withAlpha(30),
                  color: progress == 1.0 ? Colors.green : const Color(0xFF3B82F6),
                  minHeight: 6,
                  borderRadius: BorderRadius.circular(10),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}