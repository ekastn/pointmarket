import mysql.connector
from sqlalchemy import create_engine

from config import Config


def get_connection():
    try:
        return mysql.connector.connect(
            host=Config.MYSQL_HOST,
            user=Config.MYSQL_USER,
            password=Config.MYSQL_PASSWORD,
            database=Config.MYSQL_DATABASE,
        )
    except mysql.connector.Error as err:
        print(f"DB connection error: {err}")
        return None


def get_engine():
    try:
        conn_str = f"mysql+mysqlconnector://{Config.MYSQL_USER}:{Config.MYSQL_PASSWORD}@{Config.MYSQL_HOST}/{Config.MYSQL_DATABASE}"
        return create_engine(conn_str)
    except Exception as err:
        print(f"Engine creation error: {err}")
        return None


def execute_query(query: str, params=None):
    engine = get_engine()
    if not engine:
        return []
    try:
        conn = engine.raw_connection()
        cur = conn.cursor()
        if params:
            cur.execute(query, params)
        else:
            cur.execute(query)
        columns = [d[0] for d in cur.description]
        rows = [dict(zip(columns, r)) for r in cur.fetchall()]
        cur.close()
        conn.close()
        engine.dispose()
        return rows
    except Exception as e:
        print(f"Query error: {e}")
        try:
            engine.dispose()
        except Exception:
            pass
        return []
