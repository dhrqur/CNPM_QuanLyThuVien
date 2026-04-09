Usecase ID: UC - S - 01

TÊN USECASE
THÊM SÁCH

Mô tả ngắn gọn
Người dùng nhập thông tin để thêm sách mới vào hệ thống thư viện.

Tác nhân chính
Quản lý thư viện, Thủ thư

Tiền điều kiện
1. Người dùng đã đăng nhập vào hệ thống.
2. Người dùng có quyền truy cập chức năng quản lý sách.

Hậu điều kiện
1. Thêm thành công: Sách mới được lưu vào hệ thống.
2. Thêm thất bại: Dữ liệu không được lưu.

Dòng sự kiện chính
1. Người dùng chọn "Quản lý sách".
2. Hệ thống hiển thị trang sách.
3. Người dùng chọn chức năng "Thêm sách".
4. Hệ thống tự động sinh mã sách và hiển thị form nhập.
5. Người dùng nhập các thông tin:
   - Tên sách.
   - Tác giả.
   - Nhà xuất bản.
   - Thể loại.
   - Năm xuất bản.
   - Số lượng.
   - Mô tả.
   - Ngôn ngữ.
   - Vị trí.
6. Hệ thống kiểm tra tính hợp lệ của dữ liệu.
7. Hệ thống lưu thông tin sách vào cơ sở dữ liệu.
8. Hệ thống hiển thị thông báo "Thêm thành công".
9. Hoàn tất thao tác.

Luồng sự kiện phụ
Luồng phụ A: Hủy thao tác
1. B3, B4.a: Người dùng nhấn "Hủy".
2. B3, B4.b: Hệ thống quay lại màn hình "Trang sách".

Luồng phụ B: Nhập thiếu thông tin
1. B5.a: Hệ thống hiển thị "Vui lòng nhập đầy đủ thông tin".
2. B5.b: Hệ thống quay lại bước 3.
3. B5.c: Sau 3 lần sai liên tiếp, hệ thống hủy thao tác.

Luồng phụ C: Sai định dạng dữ liệu
1. B5.a: Hệ thống hiển thị "Dữ liệu không hợp lệ".
2. B5.b: Hệ thống quay lại bước 3.
3. B5.c: Sau 3 lần sai liên tiếp, hệ thống hủy thao tác.

Luồng phụ D: Năm xuất bản không hợp lệ
1. B5.a: Hệ thống hiển thị "Năm xuất bản không hợp lệ".
2. B5.b: Hệ thống quay lại bước 3.

Luồng phụ E: Số lượng không hợp lệ
1. B5.a: Hệ thống hiển thị "Số lượng phải là số nguyên dương".
2. B5.b: Hệ thống quay lại bước 3.

Luồng phụ F: Lỗi hệ thống
1. B8.a: Hệ thống hiển thị "Lỗi hệ thống".
2. B8.b: Hệ thống hủy thao tác.

Yêu cầu đặc biệt
1. Chỉ người dùng có quyền (Quản lý thư viện, Thủ thư) mới được phép thực hiện chức năng thêm sách.
2. Mã sách được hệ thống tự động sinh và đảm bảo duy nhất.
3. Tên sách không được để trống.
4. Tác giả không được để trống.
5. Nhà xuất bản không được để trống.
6. Thể loại phải tồn tại trong hệ thống.
7. Năm xuất bản phải là số hợp lệ và không lớn hơn năm hiện tại.
8. Số lượng phải là số nguyên dương.
9. Tất cả các trường bắt buộc phải được nhập đầy đủ trước khi lưu.
10. Hệ thống phải kiểm tra tính hợp lệ của toàn bộ dữ liệu trước khi thêm mới.
11. Hệ thống phải đảm bảo không xảy ra trùng mã sách.
12. Thời gian xử lý yêu cầu thêm sách không vượt quá 3 giây trong điều kiện bình thường.
13. Hệ thống phải hiển thị thông báo rõ ràng cho người dùng trong mọi trường hợp.

Ánh xạ triển khai
1. Web:
   - GET /sach (trang quản lý sách)
   - GET /sach/create (màn hình thêm sách)
   - POST /sach (lưu sách mới)
2. API:
   - POST /api/books (thêm sách mới)