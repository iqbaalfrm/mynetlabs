import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:flutter_spinkit/flutter_spinkit.dart';
import '../../theme/netlabs_theme.dart';
import '../controllers/chatbot_controller.dart';

class ChatbotView extends GetView<ChatbotController> {
  const ChatbotView({super.key});

  @override
  Widget build(BuildContext context) {
    Get.put(ChatbotController());
    return Scaffold(
      backgroundColor: NetlabsTheme.surface,
      appBar: AppBar(
        title: const Text('AI Tutor', style: TextStyle(fontWeight: FontWeight.w700, color: NetlabsTheme.textPrimary)),
        backgroundColor: NetlabsTheme.surface,
        elevation: 0,
        centerTitle: true,
      ),
      body: Column(children: [
        Expanded(child: _buildChatList()),
        Obx(() => controller.isAiTyping.value
            ? Padding(
                padding: const EdgeInsets.symmetric(vertical: 10),
                child: Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                  const SpinKitThreeBounce(color: NetlabsTheme.primary, size: 18),
                  const SizedBox(width: 8),
                  Text('AI Tutor sedang menyusun jawaban...', style: TextStyle(fontSize: 12, color: NetlabsTheme.textMuted)),
                ]),
              )
            : const SizedBox.shrink()),
        _buildSuggestionChips(),
        const SizedBox(height: 8),
        _buildInputBar(),
      ]),
    );
  }

  Widget _buildChatList() {
    return Obx(() {
      return ListView.builder(
        controller: controller.scrollCtrl,
        padding: const EdgeInsets.all(16),
        itemCount: controller.messages.length,
        itemBuilder: (context, index) {
          final chat = controller.messages[index];
          final isSiswa = chat['sender'] == 'siswa';
          return _ChatBubble(isSiswa: isSiswa, message: chat['pesan'] ?? '', source: chat['sumber']);
        },
      );
    });
  }

  Widget _buildSuggestionChips() {
    return Obx(() {
      if (controller.messages.length > 1) return const SizedBox.shrink();
      return SizedBox(
        height: 40,
        child: ListView.separated(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          scrollDirection: Axis.horizontal,
          itemCount: controller.chips.length,
          separatorBuilder: (_, __) => const SizedBox(width: 8),
          itemBuilder: (_, i) => ActionChip(
            label: Text(controller.chips[i], style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))), // Cool Slate Grey
            backgroundColor: const Color(0xFFF8FAFC),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            side: const BorderSide(color: Color(0xFFE2E8F0)),
            onPressed: () => controller.sendMessage(controller.chips[i]),
          ),
        ),
      );
    });
  }

  Widget _buildInputBar() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: NetlabsTheme.card,
        boxShadow: [BoxShadow(color: NetlabsTheme.dark.withAlpha(6), blurRadius: 10, offset: const Offset(0, -2))],
      ),
      child: Row(children: [
        Expanded(
          child: TextField(
            controller: controller.msgCtrl,
            decoration: InputDecoration(
              hintText: 'Tanyakan materi jaringan...',
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(NetlabsTheme.radiusSm), borderSide: BorderSide.none),
              filled: true, fillColor: NetlabsTheme.surface,
              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            ),
            onSubmitted: (v) => controller.sendMessage(v),
          ),
        ),
        const SizedBox(width: 8),
        Container(
          decoration: BoxDecoration(
            color: NetlabsTheme.primary,
            borderRadius: BorderRadius.circular(NetlabsTheme.radiusSm),
            boxShadow: NetlabsTheme.shadowMd,
          ),
          child: IconButton(
            icon: const Icon(Icons.send_rounded, color: Colors.white, size: 20),
            onPressed: () => controller.sendMessage(controller.msgCtrl.text),
          ),
        ),
      ]),
    );
  }
}

class _ChatBubble extends StatelessWidget {
  final bool isSiswa;
  final String message;
  final String? source;
  const _ChatBubble({required this.isSiswa, required this.message, this.source});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.end,
        mainAxisAlignment: isSiswa ? MainAxisAlignment.end : MainAxisAlignment.start,
        children: [
          if (!isSiswa) _buildAiAvatar(),
          if (!isSiswa) const SizedBox(width: 8),
          Flexible(
            child: Column(
              crossAxisAlignment: isSiswa ? CrossAxisAlignment.end : CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(14),
                  constraints: BoxConstraints(maxWidth: Get.width * 0.72),
                  decoration: BoxDecoration(
                    color: isSiswa ? const Color(0xFFEEF2FF) : const Color(0xFFF8FAFC), // Soft Indigo / Ultra Soft Slate
                    border: isSiswa ? null : Border.all(color: const Color(0xFFE2E8F0)),
                    borderRadius: BorderRadius.only(
                      topLeft: const Radius.circular(16),
                      topRight: const Radius.circular(16),
                      bottomLeft: isSiswa ? const Radius.circular(16) : const Radius.circular(4),
                      bottomRight: isSiswa ? const Radius.circular(4) : const Radius.circular(16),
                    ),
                  ),
                  child: _isCliText(message)
                      ? _buildCliBlock(message)
                      : Text(message, style: TextStyle(color: isSiswa ? const Color(0xFF312E81) : const Color(0xFF1E293B), fontSize: 14, height: 1.5)),
                ),
                if (!isSiswa && source != null) ...[
                  const SizedBox(height: 4),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(color: NetlabsTheme.success.withAlpha(15), borderRadius: BorderRadius.circular(6), border: Border.all(color: NetlabsTheme.success.withAlpha(50))),
                    child: Row(mainAxisSize: MainAxisSize.min, children: [
                      const Icon(Icons.bookmark_outline, size: 11, color: NetlabsTheme.success),
                      const SizedBox(width: 4),
                      Text(source!, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: NetlabsTheme.success)),
                    ]),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAiAvatar() {
    return Container(
      width: 32, height: 32, margin: const EdgeInsets.only(bottom: 4),
      decoration: BoxDecoration(
        color: const Color(0xFFF1F5F9), // Soft Slate Avatar Background
        border: Border.all(color: const Color(0xFFE2E8F0)),
        borderRadius: BorderRadius.circular(10), 
      ),
      child: const Icon(Icons.support_agent_rounded, color: Color(0xFF64748B), size: 18), // Tutor Icon
    );
  }

  bool _isCliText(String t) {
    return t.contains('interface ') || t.contains('ip ') || t.contains('router ') ||
        t.contains('show ') || t.contains('enable') || t.contains('conf t') ||
        t.contains('switchport') || t.contains('access-list') || t.contains('vlan ') ||
        t.contains('ping ') || t.contains('tracert');
  }

  Widget _buildCliBlock(String text) {
    final lines = text.split('\n');
    return Container(
      width: double.infinity, padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFF0F172A), // Dark Slate (Tailwind 900)
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        const Text('CLI - Cisco IOS', style: TextStyle(fontSize: 9, color: Colors.white38, fontFamily: 'monospace', fontWeight: FontWeight.w600)),
        const Divider(color: Colors.white10, height: 12),
        ...lines.map((line) {
          final isCmd = line.trimLeft().startsWith(RegExp(r'Router|Switch|#|>'));
          return Padding(
            padding: const EdgeInsets.only(bottom: 2),
            child: Text(line, style: TextStyle(fontSize: 12, fontFamily: 'monospace', height: 1.4, color: isCmd ? const Color(0xFF38BDF8) : const Color(0xFFF8FAFC))), // Sky Blue / White
          );
        }),
      ]),
    );
  }

  Widget _dot(Color c) => Container(width: 10, height: 10, decoration: BoxDecoration(color: c, shape: BoxShape.circle));
}
