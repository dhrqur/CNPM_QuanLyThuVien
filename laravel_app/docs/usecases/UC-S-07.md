Usecase ID: UC - S - 07

TÊN USECASE
XEM THÔNG TIN CHI TIẾT SÁCH

Mô tả ngắn gọn
Người dùng xem toàn bộ thông tin chi tiết của một cuốn sách trong hệ thống thư viện.

Tác nhân chính
Quản lý thư viện, Thủ thư

Tiền điều kiện
1. Người dùng đã đăng nhập vào hệ thống.
2. Người dùng có quyền truy cập chức năng quản lý sách.
3. Sách cần xem chi tiết đã tồn tại trong hệ thống.

Hậu điều kiện
1. Hiển thị đầy đủ thông tin chi tiết của sách.

Dòng sự kiện chính
1. Người dùng chọn menu "Quản lý sách".
2. Hệ thống hiển thị trang danh sách sách.
3. Người dùng chọn một sách cần xem chi tiết.
4. Người dùng nhấn nút "Xem chi tiết".
5. Hệ thống tiếp nhận yêu cầu xem thông tin sách.
6. Hệ thống lấy thông tin chi tiết của sách từ cơ sở dữ liệu.
7. Hệ thống hiển thị đầy đủ thông tin chi tiết của sách, bao gồm:
   - Mã sách.
   - Tên sách.
   - Tác giả.
   - Nhà xuất bản.
   - Thể loại.
   - Năm xuất bản.
   - Ngôn ngữ.
   - Vị trí / Kệ sách.
   - Số lượng.
   - Trạng thái sách.
   - Mô tả sách.
8. Người dùng xem thông tin chi tiết sách.
9. Hoàn tất thao tác.

Luồng sự kiện phụ
Luồng phụ A: Hủy thao tác xem chi tiết
1. B4.a: Người dùng chọn "Quay lại" hoặc "Đóng".
2. B4.b: Hệ thống quay lại màn hình danh sách sách.

Luồng phụ B: Lỗi hệ thống khi lấy thông tin sách
1. B6.a: Hệ thống hiển thị "Không thể tải thông tin chi tiết sách".
2. B6.b: Hủy thao tác xem chi tiết.

Luồng phụ C: Mất kết nối hệ thống
1. B5, B6.a: Hệ thống hiển thị "Không thể kết nối hệ thống".
2. B5, B6.b: Hủy thao tác.

Yêu cầu đặc biệt
1. Hệ thống phải hiển thị đầy đủ và chính xác thông tin của sách.
2. Thông tin hiển thị phải đúng với dữ liệu hiện có trong hệ thống.
3. Giao diện chi tiết sách phải rõ ràng, dễ theo dõi.
4. Hệ thống phải hiển thị thông báo rõ ràng cho người dùng trong mọi trường hợp.

Ánh xạ triển khai
1. Web:
   - GET /sach/{book} (trang chi tiết sách).
2. API:
   - GET /api/books/{book} (lấy chi tiết sách dạng JSON).
