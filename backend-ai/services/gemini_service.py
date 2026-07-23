import logging
import google.generativeai as genai
from config import Config

logger = logging.getLogger("NetLabsAI.GeminiService")

# Validasi API Key
Config.validate()
genai.configure(api_key=Config.GEMINI_API_KEY)

# Schema JSON untuk Structured Output Gemini Quiz
QUIZ_RESPONSE_SCHEMA = {
    "type": "array",
    "items": {
        "type": "object",
        "properties": {
            "pertanyaan": {
                "type": "string",
                "description": "Teks pertanyaan soal kuis"
            },
            "pilihan_a": {
                "type": "string",
                "description": "Teks pilihan jawaban A"
            },
            "pilihan_b": {
                "type": "string",
                "description": "Teks pilihan jawaban B"
            },
            "pilihan_c": {
                "type": "string",
                "description": "Teks pilihan jawaban C"
            },
            "pilihan_d": {
                "type": "string",
                "description": "Teks pilihan jawaban D"
            },
            "kunci_jawaban": {
                "type": "string",
                "description": "Huruf jawaban yang benar (A/B/C/D)",
                "enum": ["A", "B", "C", "D"]
            },
            "pembahasan": {
                "type": "string",
                "description": "Penjelasan mengapa jawaban tersebut benar berdasarkan modul"
            }
        },
        "required": [
            "pertanyaan", "pilihan_a", "pilihan_b",
            "pilihan_c", "pilihan_d", "kunci_jawaban", "pembahasan"
        ]
    }
}

# Inisialisasi model-model Gemini
logger.info("Menginisialisasi model Google Gemini LLM...")
_chat_model = genai.GenerativeModel(
    model_name="gemini-2.5-flash",
    generation_config=genai.GenerationConfig(
        temperature=0.3,
        top_p=0.85,
        top_k=40,
        max_output_tokens=2048,
    ),
)

_quiz_model = genai.GenerativeModel(
    model_name="gemini-2.5-flash",
    generation_config=genai.GenerationConfig(
        temperature=0.7,
        top_p=0.9,
        top_k=40,
        max_output_tokens=4096,
        response_mime_type="application/json",
        response_schema=QUIZ_RESPONSE_SCHEMA,
    ),
)
logger.info("Model Gemini berhasil diinisialisasi.")

FALLBACK_MODELS = [
    "gemini-2.5-flash",
    "gemini-2.0-flash",
    "gemini-1.5-flash",
    "gemini-1.5-flash-8b"
]

def generate_chat_response(prompt: str, user_message: str) -> str:
    """Generate balasan chatbot RAG menggunakan Gemini dengan model fallback cascade."""
    logger.info("Mengirim pesan RAG ke Gemini...")
    for model_name in FALLBACK_MODELS:
        try:
            m = genai.GenerativeModel(
                model_name=model_name,
                generation_config=genai.GenerationConfig(
                    temperature=0.3,
                    top_p=0.85,
                    max_output_tokens=2048,
                ),
            )
            response = m.generate_content(
                contents=[
                    {"role": "user", "parts": [{"text": prompt + "\n\nPertanyaan siswa: " + user_message}]},
                ],
                request_options={"timeout": 25.0}
            )
            if response and response.text:
                logger.info(f"Sukses generate_chat_response menggunakan model {model_name}.")
                return response.text.strip()
        except Exception as e:
            err_str = str(e)
            logger.warning(f"Model {model_name} gagal: {err_str}")
            if "429" in err_str or "quota" in err_str.lower() or "limit" in err_str.lower():
                logger.info(f"Model {model_name} terkena Rate Limit 429. Mencoba model fallback berikutnya...")
                continue
            else:
                continue

    return "Maaf, sistem AI sedang menerima lalu lintas pertanyaan yang sangat padat (Rate Limit 429). Silakan tunggu 10-15 detik dan coba kirim ulang pertanyaan Anda."

def generate_quiz_json(prompt: str) -> str:
    """Generate soal kuis dalam format JSON terstruktur menggunakan Gemini dengan model fallback."""
    import re
    logger.info("Mengirim instruksi pembuatan kuis ke Gemini...")
    for model_name in FALLBACK_MODELS:
        try:
            m = genai.GenerativeModel(
                model_name=model_name,
                generation_config=genai.GenerationConfig(
                    temperature=0.7,
                    top_p=0.9,
                    max_output_tokens=4096,
                    response_mime_type="application/json",
                    response_schema=QUIZ_RESPONSE_SCHEMA,
                ),
            )
            response = m.generate_content(
                contents=[
                    {"role": "user", "parts": [{"text": prompt}]},
                ],
                request_options={"timeout": 30.0}
            )
            raw_text = response.text.strip() if response and response.text else ""
            if raw_text:
                if raw_text.startswith("```"):
                    raw_text = re.sub(r'^```(?:json)?\s*', '', raw_text, flags=re.IGNORECASE)
                    raw_text = re.sub(r'\s*```$', '', raw_text).strip()
                logger.info(f"Sukses generate_quiz_json menggunakan model {model_name}.")
                return raw_text
        except Exception as e:
            err_str = str(e)
            logger.warning(f"Model {model_name} quiz generation gagal: {err_str}")
            if "429" in err_str or "quota" in err_str.lower() or "limit" in err_str.lower():
                logger.info(f"Model {model_name} terkena Rate Limit 429. Mencoba model fallback berikutnya...")
                continue
            else:
                continue

    return ""

def transcribe_audio_file(file_path: str, mime_type: str) -> str:
    """Mentranskripsikan audio file menggunakan Gemini API.
    
    Args:
        file_path (str): File path lokal sementara dari audio.
        mime_type (str): MIME type dari file audio.
        
    Returns:
        str: Teks hasil transkripsi.
    """
    logger.info(f"Mengupload file audio ke Gemini: {file_path}")
    gemini_file = genai.upload_file(file_path, mime_type=mime_type)
    
    response = _chat_model.generate_content([
        gemini_file,
        "Transkripsikan audio ini menjadi teks Bahasa Indonesia. "
        "Berikan HANYA teks hasil transkripsi tanpa penjelasan tambahan. "
        "Jika audio tidak jelas atau kosong, tulis '[audio tidak jelas]'."
    ], request_options={"timeout": 60.0})
    return response.text.strip() if response.text else "[audio tidak jelas]"
