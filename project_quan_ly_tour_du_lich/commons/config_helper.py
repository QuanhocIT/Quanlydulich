#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Helper for loading configuration from .env file."""

import os
from pathlib import Path
from typing import Any, Dict


def load_config() -> Dict[str, Any]:
    """Load environment configuration from .env file."""
    env_file = Path(__file__).parent.parent / '.env'
    config = {}

    # Load from .env file
    if env_file.exists():
        try:
            with open(env_file, 'r', encoding='utf-8') as f:
                for line in f:
                    line = line.strip()
                    if not line or line.startswith('#'):
                        continue
                    if '=' in line:
                        key, value = line.split('=', 1)
                        key = key.strip()
                        value = value.strip()
                        # Remove quotes if present
                        if value.startswith('"') and value.endswith('"'):
                            value = value[1:-1]
                        elif value.startswith("'") and value.endswith("'"):
                            value = value[1:-1]
                        config[key] = value
        except Exception as e:
            print(f'Error loading .env file: {e}')

    # Override with environment variables
    for key in ['DB_HOST', 'DB_USER', 'DB_PASSWORD', 'DB_NAME', 'DB_PORT',
                'REALTIME_WS_ENABLED', 'REALTIME_WS_HOST', 'REALTIME_WS_PORT',
                'REALTIME_HMAC_SECRET']:
        env_value = os.getenv(key)
        if env_value:
            config[key] = env_value

    return config
