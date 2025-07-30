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

    from .routes import api_bp
    app.register_blueprint(api_bp)

    return app
