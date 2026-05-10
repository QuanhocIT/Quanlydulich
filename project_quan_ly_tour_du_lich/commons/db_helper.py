#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Helper functions for database connections and operations."""

import logging
from typing import Optional

import mysql.connector
from mysql.connector import MySQLConnection

logger = logging.getLogger(__name__)


def get_db_connection() -> Optional[MySQLConnection]:
    """Create and return a MySQL database connection."""
    try:
        from commons.config_helper import load_config
        config = load_config()

        conn = mysql.connector.connect(
            host=config.get('DB_HOST', 'localhost'),
            user=config.get('DB_USER', 'root'),
            password=config.get('DB_PASSWORD', ''),
            database=config.get('DB_NAME', 'quan_ly_tour_du_lich'),
            port=int(config.get('DB_PORT', 3306)),
            connection_timeout=5,
        )
        return conn
    except Exception as e:
        logger.error(f'Database connection error: {e}')
        return None


def close_db_connection(conn: MySQLConnection) -> None:
    """Close database connection."""
    try:
        if conn and conn.is_connected():
            conn.close()
    except Exception as e:
        logger.debug(f'Error closing connection: {e}')
