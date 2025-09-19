import os

if os.getenv('APP_SETTING', 'dev') != 'prod':
    from dotenv import load_dotenv
    load_dotenv()

class Config:
    SERVER_NAME = os.getenv('SERVER_NAME', '0.0.0.0:5000')

    SECRET_KEY = os.environ.get('SECRET_KEY') or 'super-secret-key'

    MYSQL_HOST = os.environ.get('DB_HOST') or 'localhost'
    MYSQL_USER = os.environ.get('DB_USER') or 'lab'
    MYSQL_PASSWORD = os.environ.get('DB_PASSWORD') or 'password'
    MYSQL_DATABASE = os.environ.get('DB_NAME') or 'pointmarket_qlearning'

    # Q-Learning hyperparameters
    LEARNING_RATE = float(os.environ.get('LEARNING_RATE', '0.1'))
    DISCOUNT_FACTOR = float(os.environ.get('DISCOUNT_FACTOR', '0.9'))
    DEFAULT_EPISODES = int(os.environ.get('DEFAULT_EPISODES', '300'))

    DEBUG = True

    # CBF (Content-Based Filtering) reranker
    CBF_ENABLED = os.environ.get('CBF_ENABLED', 'true').lower() in ['1', 'true', 'yes']
    CBF_ALPHA = float(os.environ.get('CBF_ALPHA', '0.7'))  # weight on RL q-score when applicable
    CBF_WEIGHTS_VARK = float(os.environ.get('CBF_WEIGHTS_VARK', '0.35'))
    CBF_WEIGHTS_AMS = float(os.environ.get('CBF_WEIGHTS_AMS', '0.25'))
    CBF_WEIGHTS_MSLQ = float(os.environ.get('CBF_WEIGHTS_MSLQ', '0.20'))
    CBF_WEIGHTS_ENG = float(os.environ.get('CBF_WEIGHTS_ENG', '0.20'))
    CBF_DEBUG = os.environ.get('CBF_DEBUG', 'false').lower() in ['1', 'true', 'yes']

class ProductionConfig(Config):
    DEBUG = False
