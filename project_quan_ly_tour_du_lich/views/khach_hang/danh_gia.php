<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh giá Tour - Khách hàng</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Đánh giá Tour</h1>
        <form method="POST" action="/danh-gia/create">
            <div class="form-group">
                <label>Tour:</label>
                <select name="tour_id" required>
                    <!-- Danh sách tour sẽ được thêm vào đây -->
                </select>
            </div>
            <div class="form-group">
                <label>Đánh giá:</label>
                <textarea name="noi_dung" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label>Điểm số (1-5):</label>
                <input type="number" name="diem_so" min="1" max="5" required>
            </div>
            <button type="submit">Gửi đánh giá</button>
        </form>
    </div>
</body>
</html>


