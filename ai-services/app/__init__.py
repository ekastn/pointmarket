from flask import Flask
from config import config_by_name
from .database import db
import os
from .services.engine_service import motivational_engine

def create_app() -> Flask:
    # Get config name from environment, default to 'dev'
    config_name = os.getenv('APP_SETTING', 'dev')
    
    app = Flask(__name__)
    app.config.from_object(config_by_name[config_name])

    db.init_app(app)

    with app.app_context():
        motivational_engine.init_app(app)
        # Optional warm-up: triggers Stanza lazy loading to reduce first-request latency
        try:
            if getattr(motivational_engine.nlp_service, 'stanza_pipeline', None):
                motivational_engine.nlp_service.stanza_pipeline("Pemanasan singkat.")
                app.logger.debug("Stanza pipeline warm-up completed.")
        except Exception:
            app.logger.debug("Stanza warm-up skipped or failed.")

    from .routes import api_bp
    app.register_blueprint(api_bp)

    return app
