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

def generate_chat_response(prompt: str, user_message: str) -> str:
    """Generate balasan chatbot RAG menggunakan Gemini.
    
    Args:
        prompt (str): System prompt yang digabungkan dengan konteks.
        user_message (str): Pesan asli dari user/siswa.
        
    Returns:
        str: Balasan teks dari Gemini.
    """
    logger.info("Mengirim pesan RAG ke Gemini...")
    response = _chat_model.generate_content(
        contents=[
            {"role": "user", "parts": [{"text": prompt + "\n\nPertanyaan siswa: " + user_message}]},
        ],
        request_options={"timeout": 30.0}
    )
    return response.text.strip() if response.text else ""

def generate_quiz_json(prompt: str) -> str:
    """Generate soal kuis dalam format JSON terstruktur menggunakan Gemini.
    
    Args:
        prompt (str): Prompt kuis yang digabungkan dengan materi.
        
    Returns:
        str: Hasil generate berupa string JSON.
    """
    logger.info("Mengirim instruksi pembuatan kuis ke Gemini...")
    response = _quiz_model.generate_content(
        contents=[
            {"role": "user", "parts": [{"text": prompt}]},
        ],
        request_options={"timeout": 45.0}
    )
    return response.text.strip() if response.text else ""

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
