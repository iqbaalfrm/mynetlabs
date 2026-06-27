import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/materi_controller.dart';

class MateriView extends GetView<MateriController> {
  const MateriView({super.key});

  @override
  Widget build(BuildContext context) {
    final controller = Get.put(MateriController());

    return DefaultTabController(
      length: 2,
      child: Scaffold(
        backgroundColor: const Color(0xFFF8FAFC),
        appBar: AppBar(
          title: const Text(
            "Modul Praktikum",
            style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0F766E)),
          ),
          backgroundColor: Colors.white,
          elevation: 0,
          bottom: const TabBar(
            labelColor: Color(0xFF0D9488),
            unselectedLabelColor: Colors.grey,
            indicatorColor: Color(0xFF0D9488),
            indicatorWeight: 3,
            tabs: [
              Tab(text: "Semester 1"),
              Tab(text: "Semester 2"),
            ],
          ),
        ),
        body: Obx(() {
          if (controller.isLoading.value) {
            return const Center(child: CircularProgressIndicator(color: Color(0xFF0D9488)));
          }
          return TabBarView(
            children: [
              _buildMateriList(controller.materiSemester1),
              _buildMateriList(controller.materiSemester2),
            ],
          );
        }),
      ),
    );
  }

  Widget _buildMateriList(List<Map<String, dynamic>> materiList) {
    if (materiList.isEmpty) {
      return const Center(child: Text('Belum ada materi pada semester ini.', style: TextStyle(color: Colors.grey)));
    }
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

  Widget _buildMateriCard(Map<String, dynamic> item) {
    double progress = (item['progress'] as num?)?.toDouble() ?? 0.0;
    bool isCompleted = item['is_completed'] as bool? ?? false;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.withAlpha(30)),
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withAlpha(10),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: InkWell(
        onTap: () {
          Get.toNamed(
            '/detail-materi',
            arguments: {
              'id': item['id'],
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
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFF0D9488).withAlpha(25),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      "Pertemuan ${item['nomor']}",
                      style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF0D9488),
                      ),
                    ),
                  ),
                  Icon(
                    isCompleted ? Icons.check_circle_rounded : Icons.arrow_forward_ios_rounded,
                    size: 16,
                    color: isCompleted ? Colors.green : const Color(0xFF0D9488),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                item['judul'] as String,
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF0F766E),
                ),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Icon(Icons.layers_rounded, size: 14, color: Colors.grey.shade500),
                  const SizedBox(width: 4),
                  Text("${item['topik_count']} Topik", style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                ],
              ),
              const SizedBox(height: 16),
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
                color: progress >= 1.0 ? Colors.green : const Color(0xFF0D9488),
                minHeight: 6,
                borderRadius: BorderRadius.circular(10),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
