import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../core/theme/app_theme.dart';
import '../../controllers/quiz_controller.dart';

class QuizView extends GetView<QuizController> {
  const QuizView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: NetlabsTheme.surface,
      appBar: AppBar(
        title: const Text("Evaluasi Kuis Praktikum", style: TextStyle(fontWeight: FontWeight.bold, color: NetlabsTheme.primary)),
        backgroundColor: NetlabsTheme.card,
        elevation: 0,
        automaticallyImplyLeading: false,
      ),
      body: Obx(() {
        if (controller.isLoading.value) {
          return const Center(child: CircularProgressIndicator(color: NetlabsTheme.primary));
        }
        if (controller.daftarSoal.isEmpty) {
          return const Center(child: Text('Belum ada soal kuis untuk pertemuan ini.', style: TextStyle(color: NetlabsTheme.textSecondary)));
        }

        if (controller.isQuizFinished.value) {
          return Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Icon(Icons.stars_rounded, size: 80, color: NetlabsTheme.warning),
                const SizedBox(height: 16),
                const Text("Hasil Evaluasi Kuis", textAlign: TextAlign.center, style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: NetlabsTheme.primary)),
                const SizedBox(height: 24),
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: NetlabsTheme.card,
                    borderRadius: BorderRadius.circular(NetlabsTheme.radiusMd),
                    border: Border.all(color: NetlabsTheme.border),
                  ),
                  child: Column(
                    children: [
                      Text("${controller.nilaiAkhir.value.toInt()}", style: const TextStyle(fontSize: 48, fontWeight: FontWeight.bold, color: NetlabsTheme.primary)),
                      Text("Jawaban Benar: ${controller.jumlahBenar} dari ${controller.daftarSoal.length} Soal", style: const TextStyle(color: NetlabsTheme.textSecondary, fontSize: 13)),
                    ],
                  ),
                ),
                const SizedBox(height: 20),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: NetlabsTheme.accent.withAlpha(15),
                    borderRadius: BorderRadius.circular(NetlabsTheme.radiusSm),
                    border: Border.all(color: NetlabsTheme.accent.withAlpha(40)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.auto_awesome_rounded, color: NetlabsTheme.accent, size: 20),
                          SizedBox(width: 6),
                          Text("Rekomendasi AI Tutor:", style: TextStyle(fontWeight: FontWeight.bold, color: NetlabsTheme.accent, fontSize: 13)),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(controller.rekomendasiAi.value, style: const TextStyle(fontSize: 13, color: NetlabsTheme.textPrimary, height: 1.5)),
                    ],
                  ),
                ),
                const SizedBox(height: 32),
                ElevatedButton(
                  onPressed: () => Get.back(),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: NetlabsTheme.primary,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(NetlabsTheme.radiusSm)),
                  ),
                  child: const Text("Kembali", style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white)),
                ),
              ],
            ),
          );
        }

        var soalSekarang = controller.daftarSoal[controller.currentQuestionIndex.value];
        double progressRatio = (controller.currentQuestionIndex.value + 1) / controller.daftarSoal.length;

        var pilihan = soalSekarang['pilihan'] as Map<String, dynamic>;

        return Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text("Soal ${controller.currentQuestionIndex.value + 1} dari ${controller.daftarSoal.length}", style: const TextStyle(fontWeight: FontWeight.bold, color: NetlabsTheme.textSecondary)),
              const SizedBox(height: 8),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text("Progress", style: TextStyle(fontWeight: FontWeight.bold, color: NetlabsTheme.textSecondary)),
                  Text("${(progressRatio * 100).toInt()}%", style: const TextStyle(fontWeight: FontWeight.bold, color: NetlabsTheme.primary)),
                ],
              ),
              const SizedBox(height: 8),
              LinearProgressIndicator(
                value: progressRatio,
                backgroundColor: NetlabsTheme.border,
                color: NetlabsTheme.primary,
                minHeight: 6,
                borderRadius: BorderRadius.circular(10),
              ),
              const SizedBox(height: 30),
              Text(soalSekarang['pertanyaan']!, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: NetlabsTheme.dark, height: 1.5)),
              const SizedBox(height: 24),
              _buildOptionCard("A", pilihan['A']!, controller),
              const SizedBox(height: 12),
              _buildOptionCard("B", pilihan['B']!, controller),
              const SizedBox(height: 12),
              _buildOptionCard("C", pilihan['C']!, controller),
              const SizedBox(height: 12),
              _buildOptionCard("D", pilihan['D']!, controller),
              const Spacer(),
              ElevatedButton(
                onPressed: controller.selectedJawaban.value == "" ? null : () => controller.nextQuestion(),
                style: ElevatedButton.styleFrom(
                  backgroundColor: NetlabsTheme.primary,
                  disabledBackgroundColor: NetlabsTheme.border,
                  minimumSize: const Size(double.infinity, 50),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(NetlabsTheme.radiusSm)),
                ),
                child: Text(
                  controller.currentQuestionIndex.value == controller.daftarSoal.length - 1 ? "Selesai & Kirim" : "Lanjut Soal",
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: controller.selectedJawaban.value == "" ? NetlabsTheme.textMuted : Colors.white),
                ),
              ),
            ],
          ),
        );
      }),
    );
  }

  Widget _buildOptionCard(String key, String text, QuizController controller) {
    bool isSelected = controller.selectedJawaban.value == key;
    return Container(
      decoration: BoxDecoration(
        color: isSelected ? NetlabsTheme.primary.withAlpha(15) : NetlabsTheme.card,
        borderRadius: BorderRadius.circular(NetlabsTheme.radiusSm),
        border: Border.all(color: isSelected ? NetlabsTheme.primary : NetlabsTheme.border, width: isSelected ? 2 : 1),
      ),
      child: InkWell(
        onTap: () => controller.selectOption(key),
        borderRadius: BorderRadius.circular(NetlabsTheme.radiusSm),
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Row(
            children: [
              CircleAvatar(
                radius: 14,
                backgroundColor: isSelected ? NetlabsTheme.primary : NetlabsTheme.border,
                child: Text(key, style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: isSelected ? Colors.white : NetlabsTheme.textPrimary)),
              ),
              const SizedBox(width: 14),
              Expanded(child: Text(text, style: const TextStyle(fontSize: 14, color: NetlabsTheme.textPrimary))),
            ],
          ),
        ),
      ),
    );
  }
}
