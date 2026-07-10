import logging
from flask import Flask
from config import Config
from routes.api_routes import api_blueprint

# ─────────────────────────────────────────────────────────────────────────────
# Konfigurasi Logging Terstruktur
# ─────────────────────────────────────────────────────────────────────────────
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s -> %(message)s",
    datefmt="%Y-%m-%d %H:%M:%S",
)
logger = logging.getLogger("NetLabsAI.Main")

def create_app() -> Flask:
    """Factory function to build and configure Flask application.
    
    Returns:
        Flask: Configured application instance.
    """
    app = Flask(__name__)
    
    # Register blueprints
    app.register_blueprint(api_blueprint)
    
    return app

app = create_app()

if __name__ == "__main__":
    logger.info("=" * 60)
    logger.info("🚀 NetLabs AI Backend dimulai!")
    logger.info(f"   📡 Port           : {Config.FLASK_PORT}")
    logger.info(f"   🐛 Debug Mode     : {Config.FLASK_DEBUG}")
    logger.info(f"   📂 Qdrant Dir     : {Config.QDRANT_PERSIST_DIR}")
    logger.info(f"   📦 Collection     : {Config.QDRANT_COLLECTION_NAME}")
    logger.info(f"   🔤 Embedding      : paraphrase-multilingual-MiniLM-L12-v2")
    logger.info("=" * 60)

    app.run(
        host="0.0.0.0",
        port=Config.FLASK_PORT,
        debug=Config.FLASK_DEBUG,
    )
