Usecase ID: UC - S - 06

TÊN USECASE
HIỂN THỊ DANH SÁCH SÁCH

Mô tả ngắn gọn
Hệ thống tự động hiển thị danh sách sách khi người dùng truy cập vào chức năng quản lý sách.

Tác nhân chính
Quản lý thư viện, Thủ thư

Tiền điều kiện
1. Người dùng đã đăng nhập hệ thống.
2. Người dùng có quyền truy cập chức năng quản lý sách.

Hậu điều kiện
1. Hệ thống hiển thị danh sách sách.

Dòng sự kiện chính
1. Người dùng chọn "Quản lý sách".
2. Hệ thống chuyển đến trang sách.
3. Hệ thống tự động gửi yêu cầu lấy danh sách sách từ cơ sở dữ liệu.
4. Hệ thống nhận dữ liệu sách.
5. Hệ thống xử lý và định dạng dữ liệu.
6. Hệ thống hiển thị danh sách sách lên giao diện.
7. Hoàn tất.

Luồng sự kiện phụ
Luồng A: Không có dữ liệu
1. A1: Hệ thống hiển thị "Không có sách trong thư viện".

Luồng B: Lỗi hệ thống
1. B1: Hệ thống hiển thị "Không thể tải danh sách sách" và dừng hiển thị.

Yêu cầu đặc biệt
1. Danh sách phải được tải tự động ngay khi chọn quản lý sách.
2. Thời gian phản hồi nhanh.
3. Hỗ trợ phân trang nếu dữ liệu lớn.
4. Hiển thị các thông tin: Mã sách, Tên sách, Tác giả, Thể loại, NXB, Ngôn ngữ, Kệ sách, Năm xuất bản, Số lượng, Trạng thái.
5. Mỗi sách được hiển thị kèm theo các nút: Sửa, Xóa, Xem chi tiết.
6. Đảm bảo dữ liệu luôn đồng bộ với hệ thống.

Ánh xạ triển khai
1. Web:
   - GET /sach (trang danh sách sách có phân trang và bộ lọc).
2. API:
   - GET /api/books (danh sách sách có phân trang và lọc theo tiêu chí).
