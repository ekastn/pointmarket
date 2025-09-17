import os

from flask import Flask, jsonify

from config import Config


def create_app(config_class=Config) -> Flask:
    app = Flask(__name__)
    app.config.from_object(Config)

    # Lazy imports to avoid circular dependencies
    from blueprints.recommendations import bp as recommendations_bp
    from blueprints.students import bp as students_bp
    from blueprints.training import bp as training_bp

    app.register_blueprint(students_bp, url_prefix="/students")
    app.register_blueprint(recommendations_bp, url_prefix="/recommendations")
    app.register_blueprint(training_bp)

    @app.route("/health")
    def health():
        return jsonify(status="ok")

    return app
