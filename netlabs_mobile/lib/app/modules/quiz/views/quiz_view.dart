import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/quiz_controller.dart';

class QuizView extends GetView<QuizController> {
  const QuizView({super.key});

  @override
  Widget build(BuildContext context) {
    final controller = Get.put(QuizController());

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text("Evaluasi Kuis Praktikum", style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0F766E))),
        backgroundColor: Colors.white,
        elevation: 0,
        automaticallyImplyLeading: false,
      ),
      body: Obx(() {
        if (controller.isLoading.value) {
          return const Center(child: CircularProgressIndicator(color: Color(0xFF0D9488)));
        }
        if (controller.daftarSoal.isEmpty) {
          return const Center(child: Text('Belum ada soal kuis untuk pertemuan ini.'));
        }

        if (controller.isQuizFinished.value) {
          return Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Icon(Icons.stars_rounded, size: 80, color: Colors.orange),
                const SizedBox(height: 16),
                const Text("Hasil Evaluasi Kuis", textAlign: TextAlign.center, style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Color(0xFF0F766E))),
                const SizedBox(height: 24),
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.grey.withAlpha(30))),
                  child: Column(
                    children: [
                      Text("${controller.nilaiAkhir.value.toInt()}", style: const TextStyle(fontSize: 48, fontWeight: FontWeight.bold, color: Color(0xFF0D9488))),
                      Text("Jawaban Benar: ${controller.jumlahBenar} dari ${controller.daftarSoal.length} Soal", style: const TextStyle(color: Colors.grey, fontSize: 13)),
                    ],
                  ),
                ),
                const SizedBox(height: 20),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(color: Colors.purple.withAlpha(15), borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.purple.withAlpha(40))),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.psychology_rounded, color: Colors.purple, size: 20),
                          SizedBox(width: 6),
                          Text("Rekomendasi AI Tutor:", style: TextStyle(fontWeight: FontWeight.bold, color: Colors.purple, fontSize: 13)),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(controller.rekomendasiAi.value, style: const TextStyle(fontSize: 13, color: Colors.black87, height: 1.5)),
                    ],
                  ),
                ),
                const SizedBox(height: 32),
                ElevatedButton(
                  onPressed: () => Get.back(),
                  style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF0D9488), padding: const EdgeInsets.symmetric(vertical: 14), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
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
              Text("Soal ${controller.currentQuestionIndex.value + 1} dari ${controller.daftarSoal.length}", style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.grey)),
              const SizedBox(height: 8),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text("Progress", style: TextStyle(fontWeight: FontWeight.bold, color: Colors.grey)),
                  Text("${(progressRatio * 100).toInt()}%", style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0D9488))),
                ],
              ),
              const SizedBox(height: 8),
              LinearProgressIndicator(value: progressRatio, backgroundColor: Colors.grey.withAlpha(30), color: const Color(0xFF0D9488), minHeight: 6, borderRadius: BorderRadius.circular(10)),
              const SizedBox(height: 30),
              Text(soalSekarang['pertanyaan']!, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F766E), height: 1.5)),
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
                  backgroundColor: const Color(0xFF0D9488),
                  disabledBackgroundColor: Colors.grey.shade300,
                  minimumSize: const Size(double.infinity, 50),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: Text(
                  controller.currentQuestionIndex.value == controller.daftarSoal.length - 1 ? "Selesai & Kirim" : "Lanjut Soal",
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: controller.selectedJawaban.value == "" ? Colors.grey.shade500 : Colors.white),
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
        color: isSelected ? const Color(0xFF0D9488).withAlpha(15) : Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: isSelected ? const Color(0xFF0D9488) : Colors.grey.withAlpha(40), width: isSelected ? 2 : 1),
      ),
      child: InkWell(
        onTap: () => controller.selectOption(key),
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Row(
            children: [
              CircleAvatar(
                radius: 14,
                backgroundColor: isSelected ? const Color(0xFF0D9488) : Colors.grey.shade200,
                child: Text(key, style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: isSelected ? Colors.white : Colors.black87)),
              ),
              const SizedBox(width: 14),
              Expanded(child: Text(text, style: const TextStyle(fontSize: 14, color: Colors.black87))),
            ],
          ),
        ),
      ),
    );
  }
}
