import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:flutter_staggered_grid_view/flutter_staggered_grid_view.dart';
import '../../theme/netlabs_theme.dart';
import '../controllers/home_controller.dart';

class HomeView extends GetView<HomeController> {
  const HomeView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: NetlabsTheme.surface,
      body: SafeArea(
        child: Obx(() {
          if (controller.isLoading.value) return _buildLoading();
          if (controller.isError.value) return _buildError();
          return RefreshIndicator(
            onRefresh: () async => controller.loadDashboard(),
            color: NetlabsTheme.primary,
            child: CustomScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              slivers: [
                _buildSliverHeader(),
                _buildSliverStatCards(),
                _buildSliverLanjutBelajar(),
                _buildSliverQuickActions(),
                _buildSliverSectionTitle('Modul Tersedia', 'Lihat Semua', () => controller.bukaMateri()),
                _buildSliverHorizontalModules(),
                _buildSliverSectionTitle('Insight Jaringan', null, null),
                _buildSliverAiInsight(),
                const SliverToBoxAdapter(child: SizedBox(height: 100)),
              ],
            ),
          );
        }),
      ),
    );
  }

  SliverToBoxAdapter _buildSliverHeader() {
    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
        child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
          Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Obx(() => Text(controller.greeting.value, style: const TextStyle(fontSize: 14, color: NetlabsTheme.textSecondary))),
            const SizedBox(height: 2),
            Obx(() => Text(controller.studentName.value, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: NetlabsTheme.dark))),
            Obx(() => Text('${controller.studentClass.value}', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: NetlabsTheme.primary))),
          ]),
          Obx(() {
            final hasPhoto = controller.fotoProfilUrl.value != null;
            return GestureDetector(
              onTap: () => Get.toNamed('/profile'),
              child: Container(
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  boxShadow: NetlabsTheme.shadowSm,
                ),
                child: CircleAvatar(
                  radius: 22,
                  backgroundColor: NetlabsTheme.primary,
                  backgroundImage: hasPhoto ? NetworkImage(controller.fotoProfilUrl.value!) : null,
                  child: hasPhoto 
                    ? null 
                    : const Icon(Icons.person_rounded, size: 28, color: Colors.white),
                ),
              ),
            );
          }),
        ]),
      ),
    );
  }

  SliverToBoxAdapter _buildSliverStatCards() {
    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 12, 20, 8),
        child: Row(children: [
          Expanded(child: _statTile(Icons.check_circle_outline_rounded, 'Selesai', '${controller.totalTopikSelesai.value}/${controller.totalTopik.value}')),
          const SizedBox(width: 10),
          Expanded(child: _statTile(Icons.assignment_turned_in_outlined, 'Nilai', '${controller.rataRataNilai.value.toStringAsFixed(0)}')),
          const SizedBox(width: 10),
          Expanded(child: _statTile(Icons.chat_bubble_outline_rounded, 'AI Chat', '${controller.totalChatAI.value}')),
        ]),
      ),
    );
  }

  Widget _statTile(IconData icon, String label, String value) {
    final Color accent;
    switch (label) {
      case 'Selesai': accent = NetlabsTheme.primary; break;
      case 'Nilai':   accent = NetlabsTheme.success; break;
      case 'AI Chat': accent = NetlabsTheme.warning; break;
      default:        accent = NetlabsTheme.textMuted;
    }
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 12, offset: const Offset(0, 4))],
      ),
      child: Column(children: [
        Icon(icon, size: 20, color: accent), const SizedBox(height: 6),
        Text(value, style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: accent)),
        Text(label, style: const TextStyle(fontSize: 10, color: NetlabsTheme.textMuted)),
      ]),
    );
  }

  SliverToBoxAdapter _buildSliverSectionTitle(String title, String? action, VoidCallback? onAction) {
    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 20, 20, 10),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(title, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: NetlabsTheme.dark)),
            if (action != null)
              GestureDetector(
                onTap: onAction,
                child: Text(action, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: NetlabsTheme.primary)),
              ),
          ],
        ),
      ),
    );
  }

  SliverToBoxAdapter _buildSliverLanjutBelajar() {
    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 8),
        child: _buildLanjutBelajarCard(),
      ),
    );
  }

  SliverToBoxAdapter _buildSliverHorizontalModules() {
    return SliverToBoxAdapter(
      child: SizedBox(
        height: 145,
        child: Obx(() {
          if (controller.bentoCards.isEmpty) return const SizedBox.shrink();
          return ListView.separated(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            scrollDirection: Axis.horizontal,
            physics: const BouncingScrollPhysics(),
            itemCount: controller.bentoCards.length,
            separatorBuilder: (context, index) => const SizedBox(width: 12),
            itemBuilder: (context, index) {
              final card = controller.bentoCards[index];
              return SizedBox(
                width: 160,
                child: _BentoCard(
                  card: card, 
                  onTap: () => controller.bukaPertemuan(card), 
                  onQuizTap: card.adaKuis ? () => controller.bukaQuiz(card.id) : null
                ),
              );
            },
          );
        }),
      ),
    );
  }

  SliverToBoxAdapter _buildSliverAiInsight() {
    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20),
        child: Container(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: [NetlabsTheme.primary.withAlpha(20), NetlabsTheme.surface],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(12),
          ),
          padding: const EdgeInsets.all(14),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: NetlabsTheme.primary.withAlpha(20),
                  shape: BoxShape.circle,
                  border: Border.all(color: NetlabsTheme.border),
                ),
                child: const Icon(Icons.lightbulb_outline_rounded, color: NetlabsTheme.warning, size: 20),
              ),
              const SizedBox(width: 12),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Fakta AI Hari Ini', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: NetlabsTheme.dark)),
                    SizedBox(height: 4),
                    Text(
                      'Router bertugas menghubungkan dua atau lebih jaringan yang berbeda subnet. Tanpa router, komputer di Lab A tidak bisa nge-ping komputer di Lab B lho!',
                      style: TextStyle(fontSize: 11, color: NetlabsTheme.textMuted, height: 1.4),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildLanjutBelajarCard() {
    return Obx(() {
      final d = controller.lanjutBelajar.value;
      if (d == null) return const SizedBox.shrink();
      return Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [NetlabsTheme.primary, NetlabsTheme.primary.withOpacity(0.7)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(20),
        ),
        padding: const EdgeInsets.all(16),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3), 
              decoration: BoxDecoration(color: Colors.white.withOpacity(0.2), borderRadius: BorderRadius.circular(20)), 
              child: const Text('Lanjut Belajar', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Colors.white))
            ),
            const Spacer(), 
            const Icon(Icons.arrow_right_alt_rounded, color: Colors.white, size: 20),
          ]),
          const SizedBox(height: 10),
          Text(d['judul'] ?? 'Materi', style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Colors.white, height: 1.3), maxLines: 2, overflow: TextOverflow.ellipsis),
          const SizedBox(height: 12),
          ClipRRect(
            borderRadius: BorderRadius.circular(99), 
            child: LinearProgressIndicator(
              value: (d['progress'] as num?)?.toDouble() ?? 0, 
              backgroundColor: Colors.white.withOpacity(0.4), 
              valueColor: const AlwaysStoppedAnimation<Color>(Colors.white), 
              minHeight: 4
            )
          ),
          const SizedBox(height: 6),
          Text('${(((d['progress'] as num?)?.toDouble() ?? 0) * 100).toStringAsFixed(0)}% selesai', style: const TextStyle(fontSize: 11, color: Colors.white70)),
        ]),
      );
    });
  }

  SliverToBoxAdapter _buildSliverQuickActions() {
    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20),
        child: Row(children: [
          Expanded(child: _quickAction(Icons.chat_bubble_outline_rounded, 'Tanya AI Tutor', () => controller.bukaChatbot(), isAiTutor: true)),
          const SizedBox(width: 10),
          Expanded(child: _quickAction(Icons.book_outlined, 'Semua Materi', () => controller.bukaMateri(), isAiTutor: false)),
        ]),
      ),
    );
  }

  Widget _quickAction(IconData icon, String label, VoidCallback onTap, {required bool isAiTutor}) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap, 
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 12),
          decoration: BoxDecoration(
            color: isAiTutor ? NetlabsTheme.primary : Colors.white,
            border: isAiTutor ? null : Border.all(color: NetlabsTheme.primary),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center, 
            children: [
              Icon(icon, size: 16, color: isAiTutor ? Colors.white : NetlabsTheme.primary), 
              const SizedBox(width: 8), 
              Text(label, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: isAiTutor ? Colors.white : NetlabsTheme.primary))
            ]
          ),
        ),
      ),
    );
  }

  Widget _buildLoading() {
    return CustomScrollView(
      physics: const NeverScrollableScrollPhysics(),
      slivers: [
        _buildSliverHeader(),
        _buildSliverStatCards(),
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(20, 12, 20, 12),
            child: Container(
              height: 140,
              decoration: BoxDecoration(color: NetlabsTheme.border, borderRadius: BorderRadius.circular(NetlabsTheme.radiusXl)),
            ),
          ),
        ),
        _buildSliverQuickActions(),
        _buildSliverSectionTitle('Modul Tersedia', null, null),
        SliverToBoxAdapter(
          child: SizedBox(
            height: 145,
            child: ListView.separated(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              scrollDirection: Axis.horizontal,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: 3,
              separatorBuilder: (context, index) => const SizedBox(width: 12),
              itemBuilder: (context, index) => const SizedBox(width: 160, child: BentoCardSkeleton()),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildError() {
    return Center(
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        const Icon(Icons.cloud_off_rounded, size: 48, color: NetlabsTheme.textMuted), const SizedBox(height: 12),
        Text(controller.errorMessage.value, style: const TextStyle(color: NetlabsTheme.textSecondary), textAlign: TextAlign.center),
        const SizedBox(height: 16),
        ElevatedButton(onPressed: () => controller.loadDashboard(), child: const Text('Coba Lagi')),
      ]),
    );
  }

  String _initials(String name) {
    if (name.isEmpty) return '?';
    final p = name.trim().split(' ');
    return p.length >= 2 ? '${p[0][0]}${p[1][0]}'.toUpperCase() : name[0].toUpperCase();
  }
}

class _BentoCard extends StatelessWidget {
  final PertemuanCard card;
  final VoidCallback onTap;
  final VoidCallback? onQuizTap;
  const _BentoCard({required this.card, required this.onTap, this.onQuizTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(NetlabsTheme.radiusXl),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 12, offset: const Offset(0, 4))],
        ),
        padding: const EdgeInsets.all(16),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(color: NetlabsTheme.primary.withAlpha(20), borderRadius: BorderRadius.circular(6)),
              child: Text('Bab ${card.nomor}', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: NetlabsTheme.primary)),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Align(
                alignment: Alignment.centerRight,
                child: _AiDotBadge(status: card.aiStatus),
              ),
            ),
          ]),
          const SizedBox(height: 10),
          Text(card.judul, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: NetlabsTheme.textPrimary, height: 1.3), maxLines: 2, overflow: TextOverflow.ellipsis),
          const SizedBox(height: 12),
          ClipRRect(
            borderRadius: BorderRadius.circular(99),
            child: LinearProgressIndicator(
              value: card.progress,
              backgroundColor: NetlabsTheme.border,
              valueColor: AlwaysStoppedAnimation<Color>(card.progress >= 1 ? NetlabsTheme.success : NetlabsTheme.primary),
              minHeight: 5,
            ),
          ),
          const SizedBox(height: 6),
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Text('${(card.progress * 100).toStringAsFixed(0)}%', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: card.progress >= 1 ? NetlabsTheme.success : NetlabsTheme.textMuted)),
            if (onQuizTap != null)
              GestureDetector(
                onTap: onQuizTap,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(color: NetlabsTheme.warning.withAlpha(25), borderRadius: BorderRadius.circular(6)),
                  child: const Text('Kuis', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: NetlabsTheme.warning)),
                ),
              ),
          ]),
        ]),
      ),
    );
  }
}

class _AiDotBadge extends StatelessWidget {
  final AiStatus status;
  const _AiDotBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    if (status == AiStatus.pending || status == AiStatus.processing) {
      return const PulsatingDotWidget();
    }

    if (status == AiStatus.success) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
        decoration: BoxDecoration(
          color: NetlabsTheme.success.withAlpha(25), 
          borderRadius: BorderRadius.circular(6), 
          border: Border.all(color: NetlabsTheme.success.withAlpha(50), width: 0.5)
        ),
        child: const Row(mainAxisSize: MainAxisSize.min, children: [
          Icon(Icons.check_circle_rounded, size: 11, color: NetlabsTheme.success),
          SizedBox(width: 4),
          Flexible(child: Text('AI Siap', style: TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: NetlabsTheme.success), maxLines: 1, overflow: TextOverflow.ellipsis)),
        ]),
      );
    }

    // Default for Failed
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
      decoration: BoxDecoration(color: NetlabsTheme.danger.withAlpha(20), borderRadius: BorderRadius.circular(6)),
      child: const Row(mainAxisSize: MainAxisSize.min, children: [
        Icon(Icons.error_outline_rounded, size: 11, color: NetlabsTheme.danger),
        SizedBox(width: 4),
        Flexible(child: Text('AI Error', style: TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: NetlabsTheme.danger), maxLines: 1, overflow: TextOverflow.ellipsis)),
      ]),
    );
  }
}

class PulsatingDotWidget extends StatefulWidget {
  const PulsatingDotWidget({super.key});
  @override
  State<PulsatingDotWidget> createState() => _PulsatingDotWidgetState();
}

class _PulsatingDotWidgetState extends State<PulsatingDotWidget> with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _scaleAnimation;
  late Animation<double> _opacityAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(vsync: this, duration: const Duration(milliseconds: 1000))..repeat(reverse: true);
    _scaleAnimation = Tween<double>(begin: 0.6, end: 1.0).animate(CurvedAnimation(parent: _controller, curve: Curves.easeInOut));
    _opacityAnimation = Tween<double>(begin: 0.5, end: 1.0).animate(CurvedAnimation(parent: _controller, curve: Curves.easeInOut));
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
      decoration: BoxDecoration(
        color: NetlabsTheme.textSecondary.withAlpha(15),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: NetlabsTheme.textSecondary.withAlpha(30), width: 0.5),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          AnimatedBuilder(
            animation: _controller,
            builder: (context, child) {
              return Opacity(
                opacity: _opacityAnimation.value,
                child: Container(
                  width: 6, height: 6,
                  decoration: const BoxDecoration(color: NetlabsTheme.textSecondary, shape: BoxShape.circle),
                ),
              );
            },
          ),
          const SizedBox(width: 4),
          const Flexible(
            child: Text(
              'Mengindeks materi...',
              style: TextStyle(
                fontSize: 9, 
                fontWeight: FontWeight.w600, 
                color: NetlabsTheme.textSecondary
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }
}

class BentoCardSkeleton extends StatefulWidget {
  const BentoCardSkeleton({super.key});
  @override
  State<BentoCardSkeleton> createState() => _BentoCardSkeletonState();
}

class _BentoCardSkeletonState extends State<BentoCardSkeleton> with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _animation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(vsync: this, duration: const Duration(milliseconds: 1500))..repeat();
    _animation = Tween<double>(begin: -1, end: 2).animate(CurvedAnimation(parent: _controller, curve: Curves.easeInOutSine));
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _animation,
      builder: (context, child) {
        final offset = _animation.value;
        final shimmerGradient = LinearGradient(
          begin: Alignment(offset - 1, 0),
          end: Alignment(offset + 1, 0),
          colors: const [
            Color(0xFFF1F5F9),
            Color(0xFFFFFFFF),
            Color(0xFFF1F5F9),
          ],
          stops: const [0.0, 0.5, 1.0],
        );

        return Container(
          decoration: BoxDecoration(
            color: NetlabsTheme.card,
            borderRadius: BorderRadius.circular(NetlabsTheme.radiusXl),
            boxShadow: NetlabsTheme.shadowSm,
            border: Border.all(color: NetlabsTheme.border.withAlpha(120)),
          ),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(
                    width: 40, height: 16,
                    decoration: BoxDecoration(gradient: shimmerGradient, borderRadius: BorderRadius.circular(6)),
                  ),
                  Container(
                    width: 80, height: 16,
                    decoration: BoxDecoration(gradient: shimmerGradient, borderRadius: BorderRadius.circular(6)),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Container(width: double.infinity, height: 14, decoration: BoxDecoration(gradient: shimmerGradient, borderRadius: BorderRadius.circular(4))),
              const SizedBox(height: 4),
              Container(width: 80, height: 14, decoration: BoxDecoration(gradient: shimmerGradient, borderRadius: BorderRadius.circular(4))),
              const SizedBox(height: 16),
              Container(width: double.infinity, height: 6, decoration: BoxDecoration(gradient: shimmerGradient, borderRadius: BorderRadius.circular(99))),
              const SizedBox(height: 8),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(width: 30, height: 12, decoration: BoxDecoration(gradient: shimmerGradient, borderRadius: BorderRadius.circular(4))),
                ],
              ),
            ],
          ),
        );
      },
    );
  }
}
