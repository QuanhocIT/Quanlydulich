#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Setup script for Python automation environment
Cài đặt dependencies, kiểm tra cấu hình

Chạy:
    python scripts/setup.py
"""

import subprocess
import sys
from pathlib import Path
from typing import List, Tuple


class EnvironmentSetup:
    """Setup and verify Python environment."""

    def __init__(self):
        self.project_root = Path(__file__).parent.parent.parent
        self.project_dir = self.project_root / 'project_quan_ly_tour_du_lich'
        self.requirements_file = self.project_dir / 'requirements.txt'

    def print_header(self, text: str) -> None:
        """Print formatted header."""
        print(f"\n{'='*60}")
        print(f"  {text}")
        print(f"{'='*60}\n")

    def run_command(self, cmd: List[str], description: str = '') -> Tuple[bool, str]:
        """Run shell command and return output."""
        try:
            if description:
                print(f"  ⏳ {description}...")
            result = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                timeout=30,
            )
            if result.returncode == 0:
                if description:
                    print(f"  ✅ {description}")
                return True, result.stdout.strip()
            else:
                if description:
                    print(f"  ❌ {description}")
                return False, result.stderr.strip()
        except subprocess.TimeoutExpired:
            print(f"  ⏱️  {description} (timeout)")
            return False, 'Command timeout'
        except Exception as e:
            print(f"  ❌ {description}: {e}")
            return False, str(e)

    def check_python(self) -> bool:
        """Check Python version and availability."""
        self.print_header("Checking Python")
        
        success, version = self.run_command(
            [sys.executable, '--version'],
            'Checking Python version'
        )
        if success:
            print(f"  Version: {version}")
            return True
        return False

    def check_database(self) -> bool:
        """Check database connectivity."""
        self.print_header("Checking Database")
        
        try:
            from commons.db_helper import get_db_connection, close_db_connection
            conn = get_db_connection()
            if conn and conn.is_connected():
                print("  ✅ Database connection OK")
                close_db_connection(conn)
                return True
            else:
                print("  ❌ Database connection failed")
                return False
        except Exception as e:
            print(f"  ❌ Database check error: {e}")
            return False

    def install_dependencies(self) -> bool:
        """Install Python dependencies."""
        self.print_header("Installing Dependencies")
        
        if not self.requirements_file.exists():
            print(f"  ⚠️  requirements.txt not found at {self.requirements_file}")
            return False

        success, output = self.run_command(
            [sys.executable, '-m', 'pip', 'install', '--upgrade', 'pip'],
            'Upgrading pip'
        )
        if not success:
            return False

        success, output = self.run_command(
            [sys.executable, '-m', 'pip', 'install', '-r', str(self.requirements_file)],
            'Installing requirements'
        )
        
        if success:
            print("  ✅ All dependencies installed")
            return True
        else:
            print(f"  ⚠️  Some dependencies may have failed")
            return False

    def verify_modules(self) -> bool:
        """Verify required Python modules."""
        self.print_header("Verifying Modules")
        
        required_modules = [
            'websockets',
            'mysql',  # mysql-connector-python
            'apscheduler',
            'pandas',
        ]

        all_ok = True
        for module in required_modules:
            try:
                __import__(module)
                print(f"  ✅ {module}")
            except ImportError:
                print(f"  ❌ {module} - not installed")
                all_ok = False

        return all_ok

    def check_config(self) -> bool:
        """Check .env configuration."""
        self.print_header("Checking Configuration")
        
        try:
            from commons.config_helper import load_config
            config = load_config()
            
            required_keys = [
                'DB_HOST', 'DB_USER', 'DB_NAME', 'DB_PORT',
                'MAIL_ENABLED', 'SMTP_HOST',
                'REALTIME_WS_ENABLED', 'REALTIME_WS_HOST',
            ]
            
            all_ok = True
            for key in required_keys:
                value = config.get(key, '')
                if value:
                    print(f"  ✅ {key}")
                else:
                    print(f"  ⚠️  {key} - not configured")
                    all_ok = False
            
            return all_ok
        except Exception as e:
            print(f"  ❌ Config check error: {e}")
            return False

    def run_all(self) -> bool:
        """Run all setup checks."""
        print("\n" + "="*60)
        print("  🚀 Aventura Tours - Python Environment Setup")
        print("="*60 + "\n")

        results = {
            'Python': self.check_python(),
            'Dependencies': self.install_dependencies(),
            'Modules': self.verify_modules(),
            'Configuration': self.check_config(),
            'Database': self.check_database(),
        }

        self.print_header("Setup Summary")
        for check, result in results.items():
            status = "✅" if result else "❌"
            print(f"  {status} {check}")

        all_ok = all(results.values())
        
        print("\n" + "="*60)
        if all_ok:
            print("  ✅ All checks passed! System is ready.")
            print("\n  Next steps:")
            print("    1. Start scheduler: python scripts/scheduler.py")
            print("    2. Or setup cron: ./scripts/setup_cron.sh (Linux/Mac)")
        else:
            print("  ⚠️  Some checks failed. Please review above.")
        print("="*60 + "\n")

        return all_ok


def main():
    """Main entry point."""
    try:
        setup = EnvironmentSetup()
        success = setup.run_all()
        sys.exit(0 if success else 1)
    except Exception as e:
        print(f"\n❌ Fatal error: {e}")
        sys.exit(1)


if __name__ == '__main__':
    main()
