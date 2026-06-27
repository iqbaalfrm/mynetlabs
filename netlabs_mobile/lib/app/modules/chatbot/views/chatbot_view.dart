import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:flutter_spinkit/flutter_spinkit.dart';
import '../controllers/chatbot_controller.dart';

class ChatbotView extends GetView<ChatbotController> {
  const ChatbotView({super.key});

  @override
  Widget build(BuildContext context) {
    final controller = Get.put(ChatbotController());

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text("AI Tutor Kelompok Jaringan", style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0F766E))),
        backgroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
      ),
      body: Column(
        children: [
          // 1. Daftar Bubble Chat[cite: 1]
          Expanded(
            child: Obx(() => ListView.builder(
                  controller: controller.scrollController,
                  padding: const EdgeInsets.all(16),
                  itemCount: controller.chatMessages.length,
                  itemBuilder: (context, index) {
                    var chat = controller.chatMessages[index];
                    bool isSiswa = chat['sender'] == 'siswa';

                    return Column(
                      crossAxisAlignment: isSiswa ? CrossAxisAlignment.end : CrossAxisAlignment.start,
                      children: [
                        // Bubble balon chat
                        Container(
                          margin: const EdgeInsets.symmetric(vertical: 6),
                          padding: const EdgeInsets.all(14),
                          constraints: BoxConstraints(maxWidth: Get.width * 0.75),
                          decoration: BoxDecoration(
                            color: isSiswa ? const Color(0xFF0D9488) : Colors.white,
                            borderRadius: BorderRadius.circular(16).copyWith(
                              bottomRight: isSiswa ? const Radius.circular(0) : const Radius.circular(16),
                              topLeft: !isSiswa ? const Radius.circular(0) : const Radius.circular(16),
                            ),
                            boxShadow: [BoxShadow(color: Colors.grey.withAlpha(10), blurRadius: 5)],
                          ),
                          child: Text(
                            chat['pesan'],
                            style: TextStyle(color: isSiswa ? Colors.white : Colors.black87, fontSize: 14, height: 1.4),
                          ),
                        ),
                        // Label Referensi Sumber RAG[cite: 1]
                        if (!isSiswa && chat['sumber'] != null)
                          Padding(
                            padding: const EdgeInsets.only(left: 4, bottom: 8),
                            child: Chip(
                              avatar: const Icon(Icons.bookmark_outline, size: 12, color: Color(0xFF0D9488)),
                              label: Text(chat['sumber'], style: const TextStyle(fontSize: 10, color: Color(0xFF0D9488))),
                              backgroundColor: const Color(0xFF0D9488).withAlpha(15),
                              side: BorderSide.none,
                              visualDensity: VisualDensity.compact,
                            ),
                          ),
                      ],
                    );
                  },
                )),
          ),

          // 2. Indikator Loading AI Mengetik[cite: 1]
          Obx(() => controller.isAiTyping.value
              ? const Padding(
                  padding: EdgeInsets.symmetric(vertical: 10),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      SpinKitThreeBounce(color: Color(0xFF0D9488), size: 18),
                      SizedBox(width: 8),
                      Text("AI Tutor sedang menyusun jawaban...", style: TextStyle(fontSize: 12, color: Colors.grey)),
                    ],
                  ),
                )
              : const SizedBox()),

          // 3. Suggestion Chips (Pertanyaan Otomatis)[cite: 1]
          Obx(() => controller.chatMessages.length <= 1
              ? SizedBox(
                  height: 40,
                  child: ListView.separated(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    scrollDirection: Axis.horizontal,
                    itemCount: controller.suggestionChips.length,
                    separatorBuilder: (context, index) => const SizedBox(width: 8),
                    itemBuilder: (context, index) {
                      var chipText = controller.suggestionChips[index];
                      return ActionChip(
                        label: Text(chipText, style: const TextStyle(fontSize: 12, color: Color(0xFF0F766E))),
                        backgroundColor: Colors.white,
                        onPressed: () => controller.sendMessage(chipText),
                      );
                    },
                  ),
                )
              : const SizedBox()),
          const SizedBox(height: 8),

          // 4. Input Teks Lapangan[cite: 1]
          Container(
            padding: const EdgeInsets.all(12),
            color: Colors.white,
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: controller.messageController,
                    decoration: InputDecoration(
                      hintText: "Tanyakan kendala jaringan di sini...[cite: 1]",
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                      filled: true,
                      fillColor: const Color(0xFFF1F5F9),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                    ),
                    onSubmitted: (val) => controller.sendMessage(val),
                  ),
                ),
                const SizedBox(width: 8),
                IconButton(
                  icon: const Icon(Icons.send_rounded, color: Color(0xFF0D9488)),
                  onPressed: () => controller.sendMessage(controller.messageController.text),
                )
              ],
            ),
          )
        ],
      ),
    );
  }
}