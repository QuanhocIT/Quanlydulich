#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Realtime WebSocket Notification Server (Python)
Thay thế cho websocket_server.php

Chạy:
    python scripts/websocket_server.py

Cấu hình từ .env:
    REALTIME_WS_ENABLED=1
    REALTIME_WS_HOST=127.0.0.1
    REALTIME_WS_PORT=8765
    REALTIME_HMAC_SECRET=your-secret-key
"""

import asyncio
import json
import logging
import signal
import sys
import time
from base64 import urlsafe_b64decode
from hashlib import sha256
from hmac import compare_digest, new as hmac_new
from pathlib import Path
from typing import Any, Dict, Optional, Set

import websockets
from websockets.server import WebSocketServerProtocol

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='[%(asctime)s] %(levelname)s: %(message)s'
)
logger = logging.getLogger(__name__)

# Load config from .env
sys.path.insert(0, str(Path(__file__).parent.parent))
from commons.db_helper import get_db_connection, close_db_connection
from commons.config_helper import load_config

config = load_config()

REALTIME_WS_ENABLED = config.get('REALTIME_WS_ENABLED', False)
REALTIME_WS_HOST = config.get('REALTIME_WS_HOST', '127.0.0.1')
REALTIME_WS_PORT = int(config.get('REALTIME_WS_PORT', 8765))
REALTIME_HMAC_SECRET = config.get('REALTIME_HMAC_SECRET', '')

# Validation
if not REALTIME_WS_ENABLED:
    logger.error('REALTIME_WS_ENABLED=false, server will not start.')
    sys.exit(1)

if not REALTIME_HMAC_SECRET:
    logger.error('REALTIME_HMAC_SECRET not configured.')
    sys.exit(1)


def base64_url_decode(s: str) -> Optional[bytes]:
    """Decode base64url encoded string."""
    try:
        # Add padding if needed
        padding = 4 - len(s) % 4
        if padding != 4:
            s += '=' * padding
        return urlsafe_b64decode(s)
    except Exception:
        return None


def verify_realtime_auth_token(
    token: str, expected_scope: str = 'notifications'
) -> Optional[Dict[str, Any]]:
    """
    Verify HMAC token format:
        token = base64url(payload) . base64url(signature)
        signature = HMAC-SHA256(payload, secret)
    """
    token = token.strip()
    if '.' not in token:
        return None

    try:
        encoded_payload, encoded_signature = token.split('.', 1)
        if not encoded_payload or not encoded_signature:
            return None

        # Verify signature
        expected_signature = hmac_new(
            REALTIME_HMAC_SECRET.encode(),
            encoded_payload.encode(),
            sha256
        ).digest()
        provided_signature = base64_url_decode(encoded_signature)
        if not provided_signature or not compare_digest(
            expected_signature, provided_signature
        ):
            return None

        # Decode payload
        payload_json = base64_url_decode(encoded_payload)
        if not payload_json:
            return None

        payload = json.loads(payload_json.decode('utf-8'))
        if not isinstance(payload, dict):
            return None

        uid = int(payload.get('uid', 0))
        role = str(payload.get('role', '')).strip()
        scope = str(payload.get('scope', '')).strip()
        exp = int(payload.get('exp', 0))
        iat = int(payload.get('iat', 0))

        if uid <= 0 or not role or not scope or exp <= time.time():
            return None
        if expected_scope and scope != expected_scope:
            return None

        return {
            'user_id': uid,
            'role': role,
            'scope': scope,
            'iat': iat,
            'exp': exp,
        }
    except Exception as e:
        logger.debug(f'Token verification failed: {e}')
        return None


class RealtimeNotificationServer:
    """WebSocket server for realtime notifications."""

    def __init__(self):
        self.clients: Dict[WebSocketServerProtocol, Dict[str, Any]] = {}
        self.group_payload_hashes: Dict[str, str] = {}
        self.group_ping_at: Dict[str, float] = {}

    def build_group_key(self, metadata: Dict[str, Any]) -> str:
        """Build unique group key from role and user_id."""
        role = metadata.get('role', '')
        user_id = metadata.get('user_id', 0)
        return f"{role}:{user_id}"

    async def handle_client(
        self, websocket: WebSocketServerProtocol, path: str
    ) -> None:
        """Handle new client connection."""
        try:
            # Extract query parameters from WebSocket URI
            query_string = websocket.request_headers.get('x-query-string', '')
            params = {}
            if query_string:
                for param in query_string.split('&'):
                    if '=' in param:
                        key, value = param.split('=', 1)
                        params[key.strip()] = value.strip()

            token = params.get('token', '').strip()
            auth = verify_realtime_auth_token(token, 'notifications')
            if not auth:
                await websocket.close(code=1008, reason='Unauthorized')
                return

            metadata = {
                'user_id': auth['user_id'],
                'role': auth['role'],
                'scope': auth['scope'],
            }

            if metadata['user_id'] <= 0 or not metadata['role']:
                await websocket.close(code=1008, reason='Invalid user metadata')
                return

            self.clients[websocket] = metadata
            logger.info(
                f"Client connected: role={metadata['role']}, "
                f"user_id={metadata['user_id']}"
            )

            # Send welcome message
            welcome = {
                'type': 'welcome',
                'payload': {
                    'role': metadata['role'],
                    'user_id': metadata['user_id'],
                    'ts': int(time.time()),
                },
            }
            await websocket.send(json.dumps(welcome, ensure_ascii=False))

            # Handle incoming messages
            async for message in websocket:
                await self.handle_message(websocket, message)

        except websockets.exceptions.ConnectionClosed:
            pass
        except Exception as e:
            logger.error(f'Connection error: {e}')
        finally:
            if websocket in self.clients:
                del self.clients[websocket]
                logger.info('Client disconnected')

    async def handle_message(
        self, websocket: WebSocketServerProtocol, message: str
    ) -> None:
        """Handle message from client."""
        try:
            decoded = json.loads(message)
            msg_type = str(decoded.get('type', '')).strip()

            if msg_type != 'ping':
                return

            pong = {
                'type': 'pong',
                'payload': {'ts': int(time.time())},
            }
            await websocket.send(json.dumps(pong, ensure_ascii=False))
        except Exception as e:
            logger.debug(f'Message handling error: {e}')

    async def broadcast_tick(self) -> None:
        """Periodic tick to send notifications to all connected clients."""
        if not self.clients:
            return

        # Group clients by role:user_id
        groups: Dict[str, Dict[str, Any]] = {}
        for client, metadata in self.clients.items():
            group_key = self.build_group_key(metadata)
            if group_key not in groups:
                groups[group_key] = {'meta': metadata, 'clients': []}
            groups[group_key]['clients'].append(client)

        # Process each group
        for group_key, group in groups.items():
            meta = group['meta']
            user_id = meta.get('user_id', 0)
            role = meta.get('role', '')

            if user_id <= 0 or not role:
                continue

            try:
                # Get payload from database
                payload = await self.get_notification_payload(role, user_id)
                if not payload.get('success'):
                    continue

                message_json = json.dumps(
                    {
                        'type': 'notification',
                        'payload': payload,
                    },
                    ensure_ascii=False,
                )
                payload_hash = self._hash_message(message_json)
                last_hash = self.group_payload_hashes.get(group_key, '')
                should_ping = (
                    time.time() - self.group_ping_at.get(group_key, 0)
                ) >= 20

                if payload_hash != last_hash:
                    # New notification to send
                    await self.send_to_group(group['clients'], message_json)
                    self.group_payload_hashes[group_key] = payload_hash
                    self.group_ping_at[group_key] = time.time()
                elif should_ping:
                    # Periodic ping to keep connection alive
                    ping_msg = json.dumps(
                        {
                            'type': 'ping',
                            'payload': {'ts': int(time.time())},
                        },
                        ensure_ascii=False,
                    )
                    await self.send_to_group(group['clients'], ping_msg)
                    self.group_ping_at[group_key] = time.time()

            except Exception as e:
                logger.error(f'Broadcast tick error for group {group_key}: {e}')

    async def get_notification_payload(self, role: str, user_id: int) -> Dict[str, Any]:
        """Get notification payload from database."""
        conn = None
        try:
            conn = get_db_connection()
            if not conn:
                return {'success': False}

            if role == 'Admin':
                return await self._get_admin_notifications(conn, user_id)
            elif role == 'KhachHang':
                return await self._get_customer_notifications(conn, user_id)
            elif role == 'HDV':
                return await self._get_guide_notifications(conn, user_id)
            else:
                return {'success': False}

        except Exception as e:
            logger.error(f'Payload error: {e}')
            return {'success': False}
        finally:
            if conn:
                close_db_connection(conn)

    async def _get_admin_notifications(self, conn, user_id: int) -> Dict[str, Any]:
        """Get admin notifications."""
        try:
            cursor = conn.cursor(dictionary=True)

            # Get last seen state
            cursor.execute(
                'SELECT payments_last_seen_id, reviews_last_seen_id, sound_enabled '
                'FROM admin_notification_state WHERE admin_id = %s LIMIT 1',
                (user_id,)
            )
            state = cursor.fetchone() or {}
            payments_last_seen_id = state.get('payments_last_seen_id', 0)
            reviews_last_seen_id = state.get('reviews_last_seen_id', 0)
            sound_enabled = state.get('sound_enabled', 1)

            # Count new payments
            cursor.execute(
                'SELECT COUNT(*) as cnt FROM payments WHERE payment_id > %s',
                (payments_last_seen_id,)
            )
            payment_count = cursor.fetchone()['cnt']

            # Count new reviews
            cursor.execute(
                'SELECT COUNT(*) as cnt FROM danh_gia WHERE danh_gia_id > %s',
                (reviews_last_seen_id,)
            )
            review_count = cursor.fetchone()['cnt']

            # Count pending tour requests
            cursor.execute(
                "SELECT COUNT(*) as cnt FROM thong_bao "
                "WHERE vai_tro_nhan = 'Admin' AND tieu_de = 'Yêu cầu tour theo mong muốn' "
                "AND trang_thai = 'DaGui'"
            )
            request_count = cursor.fetchone()['cnt']

            cursor.close()

            total = payment_count + review_count + request_count
            return {
                'success': True,
                'payments': payment_count,
                'reviews': review_count,
                'requests': request_count,
                'dashboard': total,
                'sound_enabled': 1 if sound_enabled else 0,
            }
        except Exception as e:
            logger.error(f'Admin notifications error: {e}')
            return {'success': False}

    async def _get_customer_notifications(self, conn, user_id: int) -> Dict[str, Any]:
        """Get customer notifications."""
        try:
            cursor = conn.cursor(dictionary=True)

            # Count unread notifications
            cursor.execute(
                'SELECT COUNT(*) as cnt FROM thong_bao '
                'WHERE nguoi_dung_id = %s AND da_doc = 0',
                (user_id,)
            )
            unread = cursor.fetchone()['cnt']

            cursor.close()

            return {
                'success': True,
                'unread': unread,
                'items': [],
            }
        except Exception as e:
            logger.error(f'Customer notifications error: {e}')
            return {'success': False}

    async def _get_guide_notifications(self, conn, user_id: int) -> Dict[str, Any]:
        """Get guide notifications."""
        try:
            cursor = conn.cursor(dictionary=True)

            # Get guide info
            cursor.execute(
                "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = %s "
                "AND vai_tro = 'HDV' LIMIT 1",
                (user_id,)
            )
            guide = cursor.fetchone()
            if not guide:
                return {'success': False, 'unread': 0}

            guide_id = guide['nhan_su_id']

            # Count unread notifications
            cursor.execute(
                'SELECT COUNT(*) as cnt FROM thong_bao '
                'WHERE nhan_su_id = %s AND da_doc = 0',
                (guide_id,)
            )
            unread = cursor.fetchone()['cnt']

            cursor.close()

            return {
                'success': True,
                'unread': unread,
                'items': [],
            }
        except Exception as e:
            logger.error(f'Guide notifications error: {e}')
            return {'success': False}

    async def send_to_group(
        self, clients: list, message: str
    ) -> None:
        """Send message to all clients in group."""
        disconnected = []
        for client in clients:
            try:
                await client.send(message)
            except Exception as e:
                logger.debug(f'Failed to send message: {e}')
                disconnected.append(client)

        # Clean up disconnected clients
        for client in disconnected:
            if client in self.clients:
                del self.clients[client]

    @staticmethod
    def _hash_message(message: str) -> str:
        """Hash message for change detection."""
        return sha256(message.encode('utf-8')).hexdigest()


async def main():
    """Start WebSocket server."""
    server = RealtimeNotificationServer()

    # Schedule broadcast tick every 2 seconds
    async def tick_loop():
        while True:
            await asyncio.sleep(2)
            await server.broadcast_tick()

    # Create WebSocket server
    address = f'{REALTIME_WS_HOST}:{REALTIME_WS_PORT}'
    logger.info(f'Starting WebSocket server on ws://{address}')

    async with websockets.serve(
        server.handle_client,
        REALTIME_WS_HOST,
        REALTIME_WS_PORT,
        ping_interval=30,
        ping_timeout=10,
    ):
        # Run tick loop concurrently
        await tick_loop()


if __name__ == '__main__':
    try:
        asyncio.run(main())
    except KeyboardInterrupt:
        logger.info('Server stopped')
        sys.exit(0)
    except Exception as e:
        logger.error(f'Fatal error: {e}')
        sys.exit(1)
