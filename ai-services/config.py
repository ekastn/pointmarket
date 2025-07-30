import os

if os.getenv('APP_SETTING', 'dev') != 'prod':
    from dotenv import load_dotenv
    load_dotenv()


class Config:
    SERVER_NAME = os.getenv('SERVER_NAME', 'localhost:5000')
    SECRET_KEY = os.getenv('SECRET_KEY', 'super-secret')
    SQLALCHEMY_TRACK_MODIFICATIONS = False

    DB_USER = os.getenv('DB_USER')
    DB_PASSWORD = os.getenv('DB_PASSWORD')
    DB_HOST = os.getenv('DB_HOST')
    DB_PORT = os.getenv('DB_PORT')
    DB_NAME = os.getenv('DB_NAME')
    SQLALCHEMY_DATABASE_URI = f"mysql+mysqlconnector://{DB_USER}:{DB_PASSWORD}@{DB_HOST}:{DB_PORT}/{DB_NAME}"

class DevelopmentConfig(Config):
    DEVELOPMENT = True
    DEBUG = True


class ProductionConfig(Config):
    DEBUG = False


# Dictionary to map setting names to config classes
config_by_name = {
    'dev': DevelopmentConfig,
    'prod': ProductionConfig
}
