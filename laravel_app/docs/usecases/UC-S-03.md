Usecase ID: UC - S - 03

TÊN USECASE
XÓA SÁCH

Mô tả ngắn gọn
Người dùng thực hiện xóa sách khỏi hệ thống thư viện.

Tác nhân chính
Quản lý thư viện, Thủ thư

Tiền điều kiện
1. Người dùng đã đăng nhập vào hệ thống.
2. Người dùng có quyền truy cập chức năng quản lý sách.
3. Sách cần xóa đã tồn tại trong hệ thống.

Hậu điều kiện
1. Xóa thành công: Sách bị xóa khỏi hệ thống.
2. Xóa thất bại: Dữ liệu được giữ nguyên.

Dòng sự kiện chính
1. Người dùng chọn "Quản lý sách".
2. Hệ thống hiển thị trang sách.
3. Người dùng nhấn nút "Xóa" ở sách cần xóa.
4. Hệ thống hiển thị hộp thoại xác nhận xóa.
5. Người dùng xác nhận xóa.
6. Hệ thống kiểm tra các điều kiện ràng buộc.
7. Hệ thống thực hiện xóa sách khỏi cơ sở dữ liệu.
8. Hệ thống hiển thị thông báo "Xóa sách thành công".
9. Hoàn tất thao tác.

Luồng sự kiện phụ
Luồng phụ A: Hủy thao tác xóa
1. B5.a: Người dùng chọn "Hủy".
2. B5.b: Hệ thống quay lại màn hình "Trang sách".

Luồng phụ B: Sách đang được sử dụng (ràng buộc dữ liệu)
1. B7.a: Hệ thống hiển thị "Không thể xóa sách do đang được sử dụng".
2. B7.b: Hệ thống từ chối xóa.

Luồng phụ C: Lỗi khi xóa dữ liệu
1. B8.a: Hệ thống hiển thị "Xóa thất bại, vui lòng thử lại".
2. B8.b: Dữ liệu được giữ nguyên.

Yêu cầu đặc biệt
1. Chỉ người dùng có quyền (Quản lý thư viện, Thủ thư) mới được phép thực hiện chức năng xóa sách.
2. Phải có bước xác nhận trước khi thực hiện xóa để tránh thao tác nhầm.
3. Không cho phép xóa nếu sách đang được sử dụng trong hệ thống.
4. Hệ thống phải kiểm tra sự tồn tại của sách trước khi xóa.
5. Hệ thống phải đảm bảo tính toàn vẹn dữ liệu khi thực hiện xóa.
6. Thời gian xử lý yêu cầu xóa không vượt quá 3 giây trong điều kiện bình thường.
7. Hệ thống phải hiển thị thông báo rõ ràng cho người dùng trong mọi trường hợp.

Ánh xạ triển khai
1. Web:
   - DELETE /sach/{book} (xóa sách)
2. API:
   - DELETE /api/books/{book} (xóa sách)
