// Main JavaScript file

document.addEventListener("DOMContentLoaded", function () {
  // Xử lý form đặt tour
  const bookingForm = document.querySelector('form[action*="booking/create"]');
  if (bookingForm) {
    const soLuongInput = bookingForm.querySelector('input[name="so_nguoi"]');
    const tongTienInput = bookingForm.querySelector('input[name="tong_tien"]');
    const giaCoBan = parseFloat(bookingForm.dataset.giaCoBan || "0");

    if (soLuongInput && tongTienInput && !Number.isNaN(giaCoBan)) {
      const updateTongTien = () => {
        const soNguoi = parseInt(soLuongInput.value, 10) || 0;
        tongTienInput.value = (giaCoBan * soNguoi).toFixed(0);
      };

      soLuongInput.addEventListener("input", updateTongTien);
      updateTongTien();
    }
  }

  // Xử lý các thông báo
  const errorMessages = document.querySelectorAll(".error");
  errorMessages.forEach((error) => {
    setTimeout(() => {
      error.style.opacity = "0";
      setTimeout(() => error.remove(), 300);
    }, 5000);
  });
});
