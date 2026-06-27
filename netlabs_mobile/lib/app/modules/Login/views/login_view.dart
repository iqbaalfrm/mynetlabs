import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/login_controller.dart';

class LoginView extends GetView<LoginController> {
  const LoginView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24.0),
            child: Form(
              key: controller.loginFormKey, // Hubungkan dengan key form di controller
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Ikon & Identitas Aplikasi Netlabs
                  const Icon(
                    Icons.router_rounded,
                    size: 85,
                    color: Color(0xFF3B82F6), // Warna Biru Utama Netlabs
                  ),
                  const SizedBox(height: 12),
                  const Text(
                    "Netlabs",
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 30,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF1E3A8A),
                    ),
                  ),
                  const Text(
                    "ITS + LMS Praktikum Jaringan Komputer",
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 13, color: Colors.grey),
                  ),
                  const SizedBox(height: 40),

                  // Input Lapangan: NIS
                  TextFormField(
                    controller: controller.nisController,
                    keyboardType: TextInputType.number,
                    decoration: InputDecoration(
                      labelText: "Nomor Induk Siswa (NIS)",
                      prefixIcon: const Icon(Icons.assignment_ind),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    validator: (value) {
                      if (value == null || value.trim().isEmpty) {
                        return "NIS wajib diisi!";
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),

                  // Input Lapangan: Password
                  Obx(
                    () => TextFormField(
                      controller: controller.passwordController,
                      obscureText: controller.isPasswordObscured.value,
                      decoration: InputDecoration(
                        labelText: "Kata Sandi",
                        prefixIcon: const Icon(Icons.lock),
                        suffixIcon: IconButton(
                          icon: Icon(
                            controller.isPasswordObscured.value
                                ? Icons.visibility_off
                                : Icons.visibility,
                          ),
                          onPressed: controller.togglePasswordVisibility,
                        ),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
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
                  const SizedBox(height: 28),

                  // Tombol Submit Login
                  Obx(
                    () => ElevatedButton(
                      onPressed: controller.isLoading.value
                          ? null
                          : () => controller.login(),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF3B82F6), // Biru Utama
                        padding: const EdgeInsets.symmetric(vertical: 16),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      child: controller.isLoading.value
                          ? const SizedBox(
                              height: 22,
                              width: 22,
                              child: CircularProgressIndicator(
                                color: Colors.white,
                                strokeWidth: 2.5,
                              ),
                            )
                          : const Text(
                              "Masuk Ke Akun",
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: Colors.white,
                              ),
                            ),
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