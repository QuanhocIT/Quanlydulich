#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Financial Report Service (Python)
Thay thế cho BaoCaoTaiChinhService.php

Tính toán báo cáo tài chính: doanh thu, chi phí, lợi nhuận
Xuất báo cáo dạng PDF/Excel

Sử dụng Pandas/NumPy cho xử lý dữ liệu lớn
"""

import json
import logging
from datetime import datetime
from typing import Any, Dict, List, Optional, Tuple

import pandas as pd

from commons.db_helper import get_db_connection, close_db_connection

logger = logging.getLogger(__name__)


class FinancialReportService:
    """Generate financial reports."""

    def __init__(self):
        self.conn = get_db_connection()
        if not self.conn:
            raise Exception('Failed to connect to database')

    def get_dashboard_payload(
        self, tu_ngay: str, den_ngay: str
    ) -> Dict[str, Any]:
        """Get dashboard financial summary."""
        try:
            cursor = self.conn.cursor(dictionary=True)

            # Get overall summary
            cursor.execute('''
                SELECT 
                    COALESCE(SUM(so_tien), 0) as tong_thu
                FROM giao_dich
                WHERE loai = 'Thu'
                  AND ngay_giao_dich BETWEEN %s AND %s
            ''', (tu_ngay, den_ngay))
            tong_thu = float(cursor.fetchone()['tong_thu'])

            cursor.execute('''
                SELECT 
                    COALESCE(SUM(so_tien), 0) as tong_chi
                FROM giao_dich
                WHERE loai = 'Chi'
                  AND ngay_giao_dich BETWEEN %s AND %s
            ''', (tu_ngay, den_ngay))
            tong_chi = float(cursor.fetchone()['tong_chi'])

            cursor.close()

            return {
                'tong_thu': tong_thu,
                'tong_chi': tong_chi,
                'loi_nhuan': tong_thu - tong_chi,
                'top_tours': self.get_top_tours_by_revenue(5),
            }

        except Exception as e:
            logger.error(f'Dashboard payload error: {e}')
            return {'tong_thu': 0, 'tong_chi': 0, 'loi_nhuan': 0, 'top_tours': []}

    def build_tour_financial_rows(
        self, start_date: str = '', end_date: str = ''
    ) -> List[Dict[str, Any]]:
        """
        Build financial rows for all tours.
        
        Returns:
            List of tour financial data with revenue, costs, profit
        """
        try:
            cursor = self.conn.cursor(dictionary=True)

            # Get transaction summary by tour
            query = '''
                SELECT 
                    t.tour_id,
                    t.ten_tour,
                    t.loai_tour,
                    SUM(CASE WHEN gd.loai = 'Thu' THEN gd.so_tien ELSE 0 END) as tong_thu,
                    SUM(CASE WHEN gd.loai = 'Chi' THEN gd.so_tien ELSE 0 END) as tong_chi_giao_dich
                FROM tour t
                LEFT JOIN giao_dich gd ON gd.tour_id = t.tour_id
                WHERE 1=1
            '''
            params = []

            if start_date:
                query += ' AND gd.ngay_giao_dich >= %s'
                params.append(start_date)
            if end_date:
                query += ' AND gd.ngay_giao_dich <= %s'
                params.append(end_date)

            query += ' GROUP BY t.tour_id, t.ten_tour, t.loai_tour'
            query += ' ORDER BY tong_thu DESC'

            cursor.execute(query, params)
            tour_stats = cursor.fetchall()

            # Get actual costs by tour
            cost_query = '''
                SELECT 
                    tour_id,
                    COALESCE(SUM(so_tien), 0) as tong_chi_thuc_te
                FROM chi_phi_thuc_te
                WHERE trang_thai = 'DaDuyet'
            '''
            cost_params = []

            if start_date:
                cost_query += ' AND ngay_phat_sinh >= %s'
                cost_params.append(start_date)
            if end_date:
                cost_query += ' AND ngay_phat_sinh <= %s'
                cost_params.append(end_date)

            cost_query += ' GROUP BY tour_id'

            cursor.execute(cost_query, cost_params)
            costs_by_tour = {
                row['tour_id']: float(row['tong_chi_thuc_te'])
                for row in cursor.fetchall()
            }

            # Get budgets by tour
            budget_query = '''
                SELECT 
                    tour_id,
                    COALESCE(SUM(tong_du_toan), 0) as tong_du_toan
                FROM du_toan_tour
                GROUP BY tour_id
            '''

            cursor.execute(budget_query)
            budgets_by_tour = {
                row['tour_id']: float(row['tong_du_toan'])
                for row in cursor.fetchall()
            }

            cursor.close()

            # Build report rows
            rows = []
            for stat in tour_stats:
                tour_id = stat['tour_id']
                if not tour_id:
                    continue

                tong_thu = float(stat['tong_thu'] or 0)
                tong_chi_gd = float(stat['tong_chi_giao_dich'] or 0)
                tong_chi_thuc_te = costs_by_tour.get(tour_id, 0)
                tong_du_toan = budgets_by_tour.get(tour_id, 0)

                # Determine budget status
                status = 'AnToan'
                if tong_du_toan > 0:
                    if tong_chi_thuc_te > tong_du_toan:
                        status = 'VuotDuToan'
                    elif tong_chi_thuc_te >= (tong_du_toan * 0.9):
                        status = 'GanVuot'

                rows.append({
                    'tour_id': tour_id,
                    'ten_tour': stat['ten_tour'] or '',
                    'loai_tour': stat['loai_tour'] or '',
                    'tong_thu': tong_thu,
                    'tong_chi_giao_dich': tong_chi_gd,
                    'tong_chi_thuc_te': tong_chi_thuc_te,
                    'tong_du_toan': tong_du_toan,
                    'loi_nhuan': tong_thu - tong_chi_thuc_te,
                    'status': status,
                })

            return rows

        except Exception as e:
            logger.error(f'Tour financial rows error: {e}')
            return []

    def get_top_tours_by_revenue(self, limit: int = 5) -> List[Dict[str, Any]]:
        """Get top tours by revenue."""
        try:
            rows = self.build_tour_financial_rows()
            
            # Sort by revenue
            rows.sort(key=lambda r: r['tong_thu'], reverse=True)
            
            # Return top N
            return [
                {
                    'tour': {
                        'tour_id': row['tour_id'],
                        'ten_tour': row['ten_tour'],
                        'loai_tour': row['loai_tour'],
                    },
                    'doanh_thu': row['tong_thu'],
                }
                for row in rows[:limit]
            ]

        except Exception as e:
            logger.error(f'Top tours error: {e}')
            return []

    def get_giao_dich_theo_tour(self, tour_id: int) -> Dict[str, Any]:
        """Get transaction details for a specific tour."""
        try:
            cursor = self.conn.cursor(dictionary=True)

            # Get transactions
            cursor.execute('''
                SELECT 
                    id, loai, so_tien, mo_ta, ngay_giao_dich
                FROM giao_dich
                WHERE tour_id = %s
                ORDER BY ngay_giao_dich DESC
            ''', (tour_id,))
            giao_dichs = cursor.fetchall()

            # Calculate totals
            tong_thu = sum(
                float(gd['so_tien'] or 0) 
                for gd in giao_dichs 
                if gd['loai'] == 'Thu'
            )
            tong_chi_gd = sum(
                float(gd['so_tien'] or 0) 
                for gd in giao_dichs 
                if gd['loai'] == 'Chi'
            )

            # Get actual costs
            cursor.execute('''
                SELECT COALESCE(SUM(so_tien), 0) as tong
                FROM chi_phi_thuc_te
                WHERE tour_id = %s AND trang_thai = 'DaDuyet'
            ''', (tour_id,))
            tong_chi_thuc_te = float(cursor.fetchone()['tong'])

            # Get bookings
            cursor.execute('''
                SELECT 
                    b.booking_id, b.ngay_dat, b.tong_tien, b.trang_thai,
                    nd.ho_ten
                FROM booking b
                LEFT JOIN khach_hang kh ON kh.khach_hang_id = b.khach_hang_id
                LEFT JOIN nguoi_dung nd ON nd.id = kh.nguoi_dung_id
                WHERE b.tour_id = %s
                  AND (b.trang_thai IS NULL OR b.trang_thai != 'DaHuy')
                ORDER BY b.ngay_dat DESC, b.booking_id DESC
            ''', (tour_id,))
            bookings = cursor.fetchall()

            # Get tour info
            cursor.execute('''
                SELECT tour_id, ten_tour, loai_tour
                FROM tour
                WHERE tour_id = %s
            ''', (tour_id,))
            tour = cursor.fetchone()

            cursor.close()

            return {
                'giao_dichs': giao_dichs or [],
                'tour': tour or {},
                'tong_thu': tong_thu,
                'tong_chi_gd': tong_chi_gd,
                'tong_chi_thuc_te': tong_chi_thuc_te,
                'bookings': bookings or [],
                'loi_nhuan': tong_thu - tong_chi_gd - tong_chi_thuc_te,
            }

        except Exception as e:
            logger.error(f'Transaction by tour error: {e}')
            return {
                'giao_dichs': [],
                'tour': {},
                'tong_thu': 0,
                'tong_chi_gd': 0,
                'tong_chi_thuc_te': 0,
                'bookings': [],
                'loi_nhuan': 0,
            }

    def export_to_excel(
        self, start_date: str, end_date: str, output_path: str
    ) -> bool:
        """Export financial report to Excel."""
        try:
            rows = self.build_tour_financial_rows(start_date, end_date)
            
            df = pd.DataFrame(rows)
            
            # Format numeric columns
            numeric_cols = [
                'tong_thu', 'tong_chi_giao_dich', 'tong_chi_thuc_te',
                'tong_du_toan', 'loi_nhuan'
            ]
            for col in numeric_cols:
                if col in df.columns:
                    df[col] = df[col].apply(lambda x: f'{x:,.0f}')
            
            # Write to Excel
            with pd.ExcelWriter(output_path, engine='openpyxl') as writer:
                df.to_excel(
                    writer,
                    sheet_name='Báo cáo tài chính',
                    index=False
                )
            
            logger.info(f'Financial report exported to {output_path}')
            return True

        except Exception as e:
            logger.error(f'Excel export error: {e}')
            return False

    def export_to_csv(
        self, start_date: str, end_date: str, output_path: str
    ) -> bool:
        """Export financial report to CSV."""
        try:
            rows = self.build_tour_financial_rows(start_date, end_date)
            
            df = pd.DataFrame(rows)
            df.to_csv(output_path, index=False, encoding='utf-8')
            
            logger.info(f'Financial report exported to {output_path}')
            return True

        except Exception as e:
            logger.error(f'CSV export error: {e}')
            return False


def main():
    """Main entry point for testing."""
    logging.basicConfig(
        level=logging.INFO,
        format='[%(asctime)s] %(levelname)s: %(message)s'
    )

    try:
        service = FinancialReportService()

        # Example: Get dashboard summary
        payload = service.get_dashboard_payload(
            '2026-01-01', '2026-12-31'
        )
        logger.info(f'Dashboard: {json.dumps(payload, ensure_ascii=False, indent=2)}')

        # Example: Build tour financial rows
        rows = service.build_tour_financial_rows()
        logger.info(f'Tour financial rows: {len(rows)} tours')

        # Example: Export to CSV
        service.export_to_csv(
            '2026-01-01',
            '2026-12-31',
            'storage/bao_cao_tai_chinh.csv'
        )

        close_db_connection(service.conn)

    except Exception as e:
        logger.error(f'Error: {e}')


if __name__ == '__main__':
    main()
