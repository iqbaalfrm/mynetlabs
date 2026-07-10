import os
from dotenv import load_dotenv

load_dotenv()

class Config:
    """Configuration class for NetLabs AI Backend."""
    
    GEMINI_API_KEY: str = os.getenv("GEMINI_API_KEY", "")
    QDRANT_PERSIST_DIR: str = os.getenv("QDRANT_PERSIST_DIR", "./qdrant_data")
    QDRANT_COLLECTION_NAME: str = os.getenv("QDRANT_COLLECTION_NAME", "basis_pengetahuan")
    FLASK_PORT: int = int(os.getenv("FLASK_PORT", 5050))
    FLASK_DEBUG: bool = os.getenv("FLASK_DEBUG", "false").lower() == "true"

    @classmethod
    def validate(cls) -> None:
        """Validate critical configuration variables."""
        if not cls.GEMINI_API_KEY:
            raise ValueError("GEMINI_API_KEY must be set in environment or .env file.")
