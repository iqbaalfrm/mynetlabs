import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:flutter_staggered_grid_view/flutter_staggered_grid_view.dart';
import '../../../core/theme/app_theme.dart';
import '../../controllers/materi_controller.dart';

class MateriView extends GetView<MateriController> {
  const MateriView({super.key});

  @override
  Widget build(BuildContext context) {
    final controller = Get.put(MateriController());

    return DefaultTabController(
      length: 2,
      child: Scaffold(
        backgroundColor: NetlabsTheme.surface,
        appBar: AppBar(
          title: const Text(
            "Modul Praktikum",
            style: TextStyle(fontWeight: FontWeight.w800, color: NetlabsTheme.dark),
          ),
          backgroundColor: NetlabsTheme.surface,
          elevation: 0,
          bottom: PreferredSize(
            preferredSize: const Size.fromHeight(60),
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
              child: Container(
                height: 44,
                padding: const EdgeInsets.all(4),
                decoration: BoxDecoration(
                  color: NetlabsTheme.border.withAlpha(80),
                  borderRadius: BorderRadius.circular(99),
                ),
                child: TabBar(
                  indicatorSize: TabBarIndicatorSize.tab,
                  dividerColor: Colors.transparent,
                  indicator: BoxDecoration(
                    borderRadius: BorderRadius.circular(99),
                    color: NetlabsTheme.primary,
                  ),
                  labelColor: Colors.white,
                  unselectedLabelColor: NetlabsTheme.textSecondary,
                  labelStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13),
                  unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                  tabs: const [
                    Tab(text: "Semester 1"),
                    Tab(text: "Semester 2"),
                  ],
                ),
              ),
            ),
          ),
        ),
        body: Obx(() {
          if (controller.isLoading.value) {
            return const Center(child: CircularProgressIndicator(color: NetlabsTheme.primary));
          }
          return TabBarView(
            physics: const BouncingScrollPhysics(),
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
      return const Center(child: Text('Belum ada materi pada semester ini.', style: TextStyle(color: NetlabsTheme.textSecondary)));
    }
    return ListView.separated(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 100),
      physics: const BouncingScrollPhysics(),
      itemCount: materiList.length,
      separatorBuilder: (context, index) => const SizedBox(height: 16),
      itemBuilder: (context, index) {
        final item = materiList[index];
        double progress = (item['progress'] as num?)?.toDouble() ?? 0.0;
        bool isCompleted = item['is_completed'] as bool? ?? (progress >= 1.0);
        
        // Modul aktif jika belum selesai
        bool isActive = !isCompleted;
        
        return _buildBentoCard(item, isActive, progress, isCompleted);
      },
    );
  }

  Widget _buildBentoCard(Map<String, dynamic> item, bool isActive, double progress, bool isCompleted) {
    return Container(
      decoration: BoxDecoration(
        color: NetlabsTheme.card,
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: NetlabsTheme.dark.withOpacity(0.03),
            blurRadius: 20,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
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
          borderRadius: BorderRadius.circular(24),
          child: Padding(
            padding: EdgeInsets.all(isActive ? 20.0 : 16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: NetlabsTheme.primary.withAlpha(25),
                        borderRadius: BorderRadius.circular(99),
                      ),
                      child: Text(
                        "Bab ${item['nomor']}",
                        style: const TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w800,
                          color: NetlabsTheme.primary,
                        ),
                      ),
                    ),
                    if (isCompleted)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: NetlabsTheme.success.withAlpha(20),
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(color: NetlabsTheme.success.withAlpha(50), width: 0.5),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.check_circle_rounded, size: 10, color: NetlabsTheme.success),
                            SizedBox(width: 4),
                            Text("Selesai", style: TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: NetlabsTheme.success)),
                          ],
                        ),
                      )
                    else if (isActive)
                      const Icon(Icons.arrow_forward_rounded, size: 16, color: NetlabsTheme.primary)
                  ],
                ),
                SizedBox(height: isActive ? 14 : 10),
                Text(
                  item['judul'] as String,
                  style: TextStyle(
                    fontSize: isActive ? 15 : 13,
                    fontWeight: FontWeight.w800,
                    color: NetlabsTheme.dark,
                    height: 1.3,
                  ),
                  maxLines: isActive ? 3 : 2,
                  overflow: TextOverflow.ellipsis,
                ),
                SizedBox(height: isActive ? 12 : 10),
                Row(
                  children: [
                    const Icon(Icons.layers_rounded, size: 12, color: NetlabsTheme.textMuted),
                    const SizedBox(width: 4),
                    Text("${item['topik_count']} Topik", style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: NetlabsTheme.textSecondary)),
                  ],
                ),
                const SizedBox(height: 16),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text("Progress", style: TextStyle(fontSize: isActive ? 11 : 10, fontWeight: FontWeight.w600, color: NetlabsTheme.textMuted)),
                    Text("${(progress * 100).toInt()}%", style: TextStyle(fontSize: isActive ? 11 : 10, fontWeight: FontWeight.w700, color: NetlabsTheme.textPrimary)),
                  ],
                ),
                const SizedBox(height: 6),
                ClipRRect(
                  borderRadius: BorderRadius.circular(99),
                  child: LinearProgressIndicator(
                    value: progress,
                    backgroundColor: NetlabsTheme.border,
                    valueColor: AlwaysStoppedAnimation<Color>(isCompleted ? NetlabsTheme.success : NetlabsTheme.primary),
                    minHeight: 4,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
