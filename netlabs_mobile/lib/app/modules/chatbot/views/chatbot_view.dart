import 'dart:io' show File;
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:image_picker/image_picker.dart';
import 'package:flutter_spinkit/flutter_spinkit.dart';
import '../../theme/netlabs_theme.dart';
import '../controllers/chatbot_controller.dart';

class ChatbotView extends GetView<ChatbotController> {
  const ChatbotView({super.key});

  @override
  Widget build(BuildContext context) {
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
        _buildAttachmentPreview(),
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
          final msgId = (chat['id'] ?? 'idx_$index').toString();
          return _ChatBubble(
            isSiswa: isSiswa,
            message: chat['pesan'] ?? '',
            source: chat['sumber'],
            attachment: chat['attachment'] as Map<String, dynamic>?,
            messageId: msgId,
          );
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
            label: Text(controller.chips[i], style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
            backgroundColor: const Color(0xFFF8FAFC),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            side: const BorderSide(color: Color(0xFFE2E8F0)),
            onPressed: () => controller.sendMessage(controller.chips[i]),
          ),
        ),
      );
    });
  }

  Widget _buildAttachmentPreview() {
    return Obx(() {
      final att = controller.pendingAttachment.value;
      if (att == null) return const SizedBox.shrink();

      final type = att['type'] as String?;
      final path = att['path'] as String?;
      final name = att['name'] as String?;

      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        color: const Color(0xFFF8FAFC),
        child: Row(children: [
          if (type == 'image' && path != null)
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: Image.file(
                File(path),
                width: 48,
                height: 48,
                fit: BoxFit.cover,
              ),
            )
          else
            Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                color: const Color(0xFFEEF2FF),
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Icon(Icons.insert_drive_file_rounded, color: NetlabsTheme.primary, size: 24),
            ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              name ?? 'Attachment',
              style: const TextStyle(fontSize: 12, color: Color(0xFF475569)),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          IconButton(
            icon: const Icon(Icons.close, size: 18, color: Color(0xFF94A3B8)),
            onPressed: () => controller.clearAttachment(),
            padding: EdgeInsets.zero,
            constraints: const BoxConstraints(minWidth: 32, minHeight: 32),
          ),
        ]),
      );
    });
  }

  Widget _buildInputBar() {
    return Obx(() {
      final recording = controller.isRecording.value;
      if (recording) return _buildRecordingBar();

      return Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: NetlabsTheme.card,
          boxShadow: [BoxShadow(color: NetlabsTheme.dark.withAlpha(6), blurRadius: 10, offset: const Offset(0, -2))],
        ),
        child: Row(children: [
          // Attachment button
          IconButton(
            icon: const Icon(Icons.attach_file_rounded, color: Color(0xFF64748B), size: 22),
            onPressed: () => _showAttachmentSheet(),
            padding: EdgeInsets.zero,
            constraints: const BoxConstraints(minWidth: 40, minHeight: 40),
          ),
          const SizedBox(width: 4),
          Expanded(
            child: TextField(
              controller: controller.msgCtrl,
              decoration: InputDecoration(
                hintText: 'Tanyakan materi jaringan...',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(NetlabsTheme.radiusSm), borderSide: BorderSide.none),
                filled: true,
                fillColor: NetlabsTheme.surface,
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              ),
              onSubmitted: (v) => controller.sendMessage(v),
            ),
          ),
          const SizedBox(width: 4),
          // Mic button
          IconButton(
            icon: const Icon(Icons.mic_rounded, color: Color(0xFF64748B), size: 22),
            onPressed: () => controller.startRecording(),
            padding: EdgeInsets.zero,
            constraints: const BoxConstraints(minWidth: 40, minHeight: 40),
          ),
          const SizedBox(width: 4),
          // Send button
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
    });
  }

  Widget _buildRecordingBar() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [BoxShadow(color: NetlabsTheme.dark.withAlpha(6), blurRadius: 10, offset: const Offset(0, -2))],
      ),
      child: Row(children: [
        // Cancel
        IconButton(
          icon: const Icon(Icons.close_rounded, color: Color(0xFFEF4444), size: 22),
          onPressed: () => controller.cancelRecording(),
          padding: EdgeInsets.zero,
          constraints: const BoxConstraints(minWidth: 40, minHeight: 40),
        ),
        const SizedBox(width: 8),
        // Recording indicator
        Expanded(
          child: Row(children: [
            _blinkingDot(),
            const SizedBox(width: 10),
            Obx(() {
              final dur = controller.recordDuration.value;
              final m = dur ~/ 60;
              final s = dur % 60;
              return Text(
                '${m.toString().padLeft(2, '0')}:${s.toString().padLeft(2, '0')}',
                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: Color(0xFFEF4444), fontFamily: 'monospace'),
              );
            }),
            const SizedBox(width: 12),
            Expanded(
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: List.generate(15, (i) {
                  final h = 6.0 + (i % 3) * 8.0;
                  return Container(
                    width: 3,
                    height: h,
                    margin: const EdgeInsets.symmetric(horizontal: 1),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEF4444).withAlpha(100 + (i * 10).clamp(0, 155).toInt()),
                      borderRadius: BorderRadius.circular(2),
                    ),
                  );
                }),
              ),
            ),
          ]),
        ),
        // Send recording
        GestureDetector(
          onTap: () => controller.stopRecording(),
          child: Container(
            width: 44,
            height: 44,
            decoration: const BoxDecoration(
              color: NetlabsTheme.primary,
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.send_rounded, color: Colors.white, size: 20),
          ),
        ),
      ]),
    );
  }

  Widget _blinkingDot() {
    return TweenAnimationBuilder<double>(
      tween: Tween(begin: 0.4, end: 1.0),
      duration: const Duration(milliseconds: 600),
      builder: (_, val, child) {
        return Opacity(
          opacity: val,
          child: Container(
            width: 12, height: 12,
            decoration: const BoxDecoration(color: Color(0xFFEF4444), shape: BoxShape.circle),
          ),
        );
      },
      onEnd: () {},
    );
  }

  void _showAttachmentSheet() {
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
            Container(
              width: 36,
              height: 4,
              decoration: BoxDecoration(color: const Color(0xFFE2E8F0), borderRadius: BorderRadius.circular(2)),
            ),
            const SizedBox(height: 16),
            const Text('Lampirkan', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: Color(0xFF1E293B))),
            const SizedBox(height: 20),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                _attachmentOption(Icons.camera_alt_rounded, 'Kamera', () {
                  Get.back();
                  controller.pickImage(ImageSource.camera);
                }),
                _attachmentOption(Icons.photo_library_rounded, 'Galeri', () {
                  Get.back();
                  controller.pickImage(ImageSource.gallery);
                }),
                _attachmentOption(Icons.description_rounded, 'Dokumen', () {
                  Get.back();
                  controller.pickFile();
                }),
              ],
            ),
            const SizedBox(height: 20),
          ],
        ),
      ),
      backgroundColor: Colors.transparent,
      isScrollControlled: false,
    );
  }

  Widget _attachmentOption(IconData icon, String label, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Column(children: [
        Container(
          width: 56,
          height: 56,
          decoration: BoxDecoration(
            color: const Color(0xFFEEF2FF),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Icon(icon, color: NetlabsTheme.primary, size: 26),
        ),
        const SizedBox(height: 8),
        Text(label, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF475569))),
      ]),
    );
  }
}

class _ChatBubble extends StatelessWidget {
  final bool isSiswa;
  final String message;
  final String? source;
  final Map<String, dynamic>? attachment;
  final String messageId;

  const _ChatBubble({
    required this.isSiswa,
    required this.message,
    this.source,
    this.attachment,
    required this.messageId,
  });

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
                // Attachment preview in bubble
                if (isSiswa && attachment != null)
                  _buildAttachmentBubble(attachment!),
                Container(
                  padding: const EdgeInsets.all(14),
                  constraints: BoxConstraints(maxWidth: Get.width * 0.72),
                  decoration: BoxDecoration(
                    color: isSiswa ? const Color(0xFFEEF2FF) : const Color(0xFFF8FAFC),
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
                // Source badge
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
                // TTS button for AI messages
                if (!isSiswa) ...[
                  const SizedBox(height: 4),
                  Obx(() {
                    final ttsCtrl = Get.find<ChatbotController>();
                    final isActive = ttsCtrl.speakingMessageId.value == messageId && ttsCtrl.isSpeaking.value;
                    return GestureDetector(
                      onTap: () => ttsCtrl.speakMessage(messageId, message),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 4),
                        decoration: BoxDecoration(
                           color: NetlabsTheme.primary.withAlpha(12),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              isActive ? Icons.stop_rounded : Icons.volume_up_rounded,
                              size: 14,
                               color: NetlabsTheme.primary,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              isActive ? 'Stop' : 'Dengarkan',
                              style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: NetlabsTheme.primary),
                            ),
                          ],
                        ),
                      ),
                    );
                  }),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAttachmentBubble(Map<String, dynamic> att) {
    final type = att['type'] as String?;
    final path = att['path'] as String?;
    final name = att['name'] as String?;
    final duration = att['duration'] as int?;

    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: GestureDetector(
        onTap: type == 'image' && path != null
            ? () {
                Get.dialog(
                  Dialog(
                    backgroundColor: Colors.transparent,
                    child: Stack(
                      alignment: Alignment.topRight,
                      children: [
                        ClipRRect(
                          borderRadius: BorderRadius.circular(16),
                          child: Image.file(File(path), fit: BoxFit.contain),
                        ),
                        IconButton(
                          icon: const Icon(Icons.close, color: Colors.white, size: 28),
                          onPressed: () => Get.back(),
                        ),
                      ],
                    ),
                  ),
                );
              }
            : null,
        child: Container(
          constraints: BoxConstraints(maxWidth: Get.width * 0.55),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFCBD5E1)),
          ),
          child: type == 'image' && path != null
              ? ClipRRect(
                  borderRadius: BorderRadius.circular(11),
                  child: Image.file(File(path), width: double.infinity, fit: BoxFit.cover),
                )
              : type == 'audio'
                  ? Padding(
                      padding: const EdgeInsets.all(12),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.mic_rounded, color: NetlabsTheme.primary, size: 22),
                          const SizedBox(width: 10),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text(
                                name ?? 'Voice Note',
                                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF1E293B)),
                              ),
                              if (duration != null)
                                Text(
                                  _formatDuration(duration),
                                  style: const TextStyle(fontSize: 10, color: Color(0xFF94A3B8)),
                                ),
                            ],
                          ),
                        ],
                      ),
                    )
                  : Padding(
                      padding: const EdgeInsets.all(10),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.description_rounded, color: NetlabsTheme.primary, size: 22),
                          const SizedBox(width: 8),
                          Flexible(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(
                                  name ?? 'File',
                                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF1E293B)),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                if (att['size'] != null)
                                  Text(
                                    _formatSize(att['size'] as int),
                                    style: const TextStyle(fontSize: 10, color: Color(0xFF94A3B8)),
                                  ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
        ),
      ),
    );
  }

  String _formatSize(int bytes) {
    if (bytes < 1024) return '$bytes B';
    if (bytes < 1024 * 1024) return '${(bytes / 1024).toStringAsFixed(1)} KB';
    return '${(bytes / (1024 * 1024)).toStringAsFixed(1)} MB';
  }

  String _formatDuration(int seconds) {
    final m = seconds ~/ 60;
    final s = seconds % 60;
    return '${m.toString().padLeft(2, '0')}:${s.toString().padLeft(2, '0')}';
  }

  Widget _buildAiAvatar() {
    return Container(
      width: 32, height: 32, margin: const EdgeInsets.only(bottom: 4),
      decoration: BoxDecoration(
        color: const Color(0xFFF1F5F9),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        borderRadius: BorderRadius.circular(10),
      ),
      child: const Icon(Icons.support_agent_rounded, color: Color(0xFF64748B), size: 18),
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
        color: const Color(0xFF0F172A),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        const Text('CLI - Cisco IOS', style: TextStyle(fontSize: 9, color: Colors.white38, fontFamily: 'monospace', fontWeight: FontWeight.w600)),
        const Divider(color: Colors.white10, height: 12),
        ...lines.map((line) {
          final isCmd = line.trimLeft().startsWith(RegExp(r'Router|Switch|#|>'));
          return Padding(
            padding: const EdgeInsets.only(bottom: 2),
            child: Text(line, style: TextStyle(fontSize: 12, fontFamily: 'monospace', height: 1.4, color: isCmd ? const Color(0xFF38BDF8) : const Color(0xFFF8FAFC))),
          );
        }),
      ]),
    );
  }

  Widget _dot(Color c) => Container(width: 10, height: 10, decoration: BoxDecoration(color: c, shape: BoxShape.circle));
}
