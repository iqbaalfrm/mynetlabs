import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../theme/netlabs_theme.dart';
import '../controllers/login_controller.dart';

class LoginView extends GetView<LoginController> {
  const LoginView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: NetlabsTheme.surface,
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24.0),
            child: Form(
              key: controller.loginFormKey,
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // ==========================================
                  // BRANDING SECTION
                  // ==========================================
                  Center(
                    child: Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        color: NetlabsTheme.primary.withAlpha(25),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.router_rounded,
                        size: 60,
                        color: NetlabsTheme.primary,
                      ),
                    ),
                  ),
                  const SizedBox(height: 18),
                  const Text(
                    "Netlabs",
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 32,
                      fontWeight: FontWeight.w900,
                      color: NetlabsTheme.dark,
                      letterSpacing: -0.5,
                    ),
                  ),
                  const SizedBox(height: 4),
                  const Text(
                    "ITS + LMS Praktikum Jaringan Komputer",
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 13,
                      color: NetlabsTheme.textSecondary,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  const SizedBox(height: 36),

                  // ==========================================
                  // FORM CONTAINER (WHITE CARD)
                  // ==========================================
                  Container(
                    padding: const EdgeInsets.all(24),
                    decoration: BoxDecoration(
                      color: NetlabsTheme.card,
                      borderRadius: BorderRadius.circular(NetlabsTheme.radiusXl),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.04),
                          blurRadius: 20,
                          offset: const Offset(0, 8),
                        ),
                      ],
                      border: Border.all(color: NetlabsTheme.border),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        const Text(
                          "Selamat Datang",
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w800,
                            color: NetlabsTheme.dark,
                          ),
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          "Silakan masuk menggunakan NIS Anda.",
                          style: TextStyle(
                            fontSize: 12,
                            color: NetlabsTheme.textSecondary,
                          ),
                        ),
                        const SizedBox(height: 24),

                        // Input: NIS
                        TextFormField(
                          controller: controller.nisController,
                          keyboardType: TextInputType.number,
                          style: const TextStyle(fontSize: 14),
                          decoration: InputDecoration(
                            labelText: "Nomor Induk Siswa (NIS)",
                            labelStyle: const TextStyle(color: NetlabsTheme.textSecondary, fontSize: 13),
                            prefixIcon: const Icon(Icons.assignment_ind_outlined, size: 20, color: NetlabsTheme.textSecondary),
                            floatingLabelBehavior: FloatingLabelBehavior.auto,
                            filled: true,
                            fillColor: NetlabsTheme.surface,
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(NetlabsTheme.radiusSm),
                              borderSide: const BorderSide(color: NetlabsTheme.border),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(NetlabsTheme.radiusSm),
                              borderSide: const BorderSide(color: NetlabsTheme.primary, width: 1.5),
                            ),
                            errorBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(NetlabsTheme.radiusSm),
                              borderSide: const BorderSide(color: NetlabsTheme.danger),
                            ),
                            focusedErrorBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(NetlabsTheme.radiusSm),
                              borderSide: const BorderSide(color: NetlabsTheme.danger, width: 1.5),
                            ),
                            contentPadding: const EdgeInsets.symmetric(vertical: 16),
                          ),
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) {
                              return "NIS wajib diisi!";
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 16),

                        // Input: Password
                        Obx(
                          () => TextFormField(
                            controller: controller.passwordController,
                            obscureText: controller.isPasswordObscured.value,
                            style: const TextStyle(fontSize: 14),
                            decoration: InputDecoration(
                              labelText: "Kata Sandi",
                              labelStyle: const TextStyle(color: NetlabsTheme.textSecondary, fontSize: 13),
                              prefixIcon: const Icon(Icons.lock_outline_rounded, size: 20, color: NetlabsTheme.textSecondary),
                              suffixIcon: IconButton(
                                icon: Icon(
                                  controller.isPasswordObscured.value
                                      ? Icons.visibility_off_outlined
                                      : Icons.visibility_outlined,
                                  size: 20,
                                  color: NetlabsTheme.textSecondary,
                                ),
                                onPressed: controller.togglePasswordVisibility,
                              ),
                              floatingLabelBehavior: FloatingLabelBehavior.auto,
                              filled: true,
                              fillColor: NetlabsTheme.surface,
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(NetlabsTheme.radiusSm),
                                borderSide: const BorderSide(color: NetlabsTheme.border),
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(NetlabsTheme.radiusSm),
                                borderSide: const BorderSide(color: NetlabsTheme.primary, width: 1.5),
                              ),
                              errorBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(NetlabsTheme.radiusSm),
                                borderSide: const BorderSide(color: NetlabsTheme.danger),
                              ),
                              focusedErrorBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(NetlabsTheme.radiusSm),
                                borderSide: const BorderSide(color: NetlabsTheme.danger, width: 1.5),
                              ),
                              contentPadding: const EdgeInsets.symmetric(vertical: 16),
                            ),
                            validator: (value) {
                              if (value == null || value.isEmpty) {
                                return "Kata sandi wajib diisi!";
                              }
                              if (value.length < 6) {
                                return "Kata sandi minimal 6 karakter!";
                              }
                              return null;
                            },
                          ),
                        ),
                        const SizedBox(height: 24),

                        // Tombol Submit Login
                        Obx(
                          () => ElevatedButton(
                            onPressed: controller.isLoading.value
                                ? null
                                : () => controller.login(),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: NetlabsTheme.primary,
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(vertical: 16),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(NetlabsTheme.radiusSm),
                              ),
                              elevation: 2,
                              shadowColor: NetlabsTheme.primary.withOpacity(0.3),
                            ),
                            child: controller.isLoading.value
                                ? const SizedBox(
                                    height: 20,
                                    width: 20,
                                    child: CircularProgressIndicator(
                                      color: Colors.white,
                                      strokeWidth: 2.0,
                                    ),
                                  )
                                : const Text(
                                    "Masuk Ke Akun",
                                    style: TextStyle(
                                      fontSize: 15,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),

                  // ==========================================
                  // FORGOT PASSWORD INFO BANNER
                  // ==========================================
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: NetlabsTheme.primary.withAlpha(15),
                      borderRadius: BorderRadius.circular(NetlabsTheme.radiusMd),
                      border: Border.all(color: NetlabsTheme.primary.withAlpha(40)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.info_outline_rounded, size: 20, color: NetlabsTheme.primary),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            "Lupa kata sandi? Silakan hubungi Guru / Wali Kelas Anda di sekolah untuk mereset sandi Anda.",
                            style: TextStyle(
                              fontSize: 11,
                              color: NetlabsTheme.primaryDark.withOpacity(0.8),
                              height: 1.4,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
