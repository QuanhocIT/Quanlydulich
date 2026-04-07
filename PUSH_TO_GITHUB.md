# Hướng dẫn đẩy dự án lên GitHub

## Bước 1: Tạo repository trên GitHub

1. Đăng nhập vào GitHub: https://github.com
2. Click vào dấu **+** ở góc trên bên phải
3. Chọn **New repository**
4. Điền thông tin:
   - **Repository name**: `du-an-1` (hoặc tên bạn muốn)
   - **Description**: Mô tả dự án (tùy chọn)
   - Chọn **Public** hoặc **Private**
   - **KHÔNG** tích vào "Initialize this repository with a README" (vì đã có file README rồi)
5. Click **Create repository**

## Bước 2: Thêm remote và push code

Sau khi tạo repository trên GitHub, bạn sẽ thấy URL của repository (ví dụ: `https://github.com/username/du-an-1.git`)

Chạy các lệnh sau (thay `YOUR_GITHUB_URL` bằng URL repository của bạn):

```bash
cd "/Applications/XAMPP/xamppfiles/htdocs/tunganh/dự án 1"
git remote add origin YOUR_GITHUB_URL
git branch -M main
git push -u origin main
```

## Hoặc sử dụng SSH (nếu đã setup SSH key):

```bash
git remote add origin git@github.com:username/du-an-1.git
git branch -M main
git push -u origin main
```

## Lưu ý:

- Nếu GitHub yêu cầu xác thực, bạn có thể cần:
  - Sử dụng Personal Access Token thay vì password
  - Hoặc setup SSH key cho GitHub

