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
        title: const Text("Evaluasi Kuis Praktikum", style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1E3A8A))),
        backgroundColor: Colors.white,
        elevation: 0,
        automaticallyImplyLeading: false,
      ),
      body: Obx(() {
        // TAMPILAN JIKA KUIS SUDAH SELESAI[cite: 1]
        if (controller.isQuizFinished.value) {
          return Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Icon(Icons.stars_rounded, size: 80, color: Colors.orange),
                const SizedBox(height: 16),
                const Text("Hasil Evaluasi Kuis", textAlign: TextAlign.center, style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Color(0xFF1E3A8A))),
                const SizedBox(height: 24),
                
                // Papan Skor Nilai[cite: 1]
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.grey.withAlpha(30))),
                  child: Column(
                    children: [
                      Text("${controller.nilaiAkhir.value.toInt()}", style: const TextStyle(fontSize: 48, fontWeight: FontWeight.bold, color: Color(0xFF3B82F6))),
                      Text("Jawaban Benar: ${controller.jumlahBenar} dari ${controller.daftarSoal.length} Soal[cite: 1]", style: const TextStyle(color: Colors.grey, fontSize: 13)),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // Kotak Saran Rekomendasi AI Tutor[cite: 1]
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
                  onPressed: () => Get.offAllNamed('/main-layout'),
                  style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF3B82F6), padding: const EdgeInsets.symmetric(vertical: 14), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
                  child: const Text("Kembali ke Beranda", style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                )
              ],
            ),
          );
        }

        // TAMPILAN PROSES MENGERJAKAN SOAL KUIS[cite: 1]
        var soalSekarang = controller.daftarSoal[controller.currentQuestionIndex.value];
        double progressRatio = (controller.currentQuestionIndex.value + 1) / controller.daftarSoal.length;

        return Padding(
          padding: const EdgeInsets.all(20.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Progress Line Bar[cite: 1]
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text("Soal ${controller.currentQuestionIndex.value + 1} dari ${controller.daftarSoal.length}[cite: 1]", style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.grey)),
                  Text("${(progressRatio * 100).toInt()}%", style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF3B82F6))),
                ],
              ),
              const SizedBox(height: 8),
              LinearProgressIndicator(value: progressRatio, backgroundColor: Colors.grey.withAlpha(30), color: const Color(0xFF3B82F6), minHeight: 6, borderRadius: BorderRadius.circular(10)),
              const SizedBox(height: 30),

              // Teks Lembar Pertanyaan[cite: 1]
              Text(soalSekarang['pertanyaan']!, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E3A8A), height: 1.5)),
              const SizedBox(height: 24),

              // Daftar Pilihan Ganda (A, B, C, D)[cite: 1]
              _buildOptionCard("A", soalSekarang['A']!, controller),
              const SizedBox(height: 12),
              _buildOptionCard("B", soalSekarang['B']!, controller),
              const SizedBox(height: 12),
              _buildOptionCard("C", soalSekarang['C']!, controller),
              const SizedBox(height: 12),
              _buildOptionCard("D", soalSekarang['D']!, controller),
              
              const Spacer(),

              // Tombol Konfirmasi / Selanjutnya[cite: 1]
              ElevatedButton(
                onPressed: controller.selectedJawaban.value == "" ? null : () => controller.nextQuestion(), // Locked jika null[cite: 1]
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF3B82F6),
                  disabledBackgroundColor: Colors.grey.shade300,
                  minimumSize: const Size(double.infinity, 50),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: Text(
                  controller.currentQuestionIndex.value == controller.daftarSoal.length - 1 ? "Selesai & Kirim[cite: 1]" : "Lanjut Soal[cite: 1]",
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: controller.selectedJawaban.value == "" ? Colors.grey.shade500 : Colors.white),
                ),
              ),
            ],
          ),
        );
      }),
    );
  }

  // Helper Pembuat Kartu Pilihan Jawaban
  Widget _buildOptionCard(String key, String text, QuizController controller) {
    bool isSelected = controller.selectedJawaban.value == key;

    return Container(
      decoration: BoxDecoration(
        color: isSelected ? const Color(0xFF3B82F6).withAlpha(15) : Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: isSelected ? const Color(0xFF3B82F6) : Colors.grey.withAlpha(40), width: isSelected ? 2 : 1),
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
                backgroundColor: isSelected ? const Color(0xFF3B82F6) : Colors.grey.shade200,
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