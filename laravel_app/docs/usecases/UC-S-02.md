Usecase ID: UC - S - 02

TÊN USECASE
CẬP NHẬT SÁCH

Mô tả ngắn gọn
Người dùng chỉnh sửa và cập nhật thông tin của sách trong hệ thống.

Tác nhân chính
Quản lý thư viện, Thủ thư

Tiền điều kiện
1. Người dùng đã đăng nhập vào hệ thống.
2. Người dùng có quyền truy cập chức năng quản lý sách.
3. Sách cần cập nhật đã tồn tại trong hệ thống.

Hậu điều kiện
1. Cập nhật thành công: Thông tin sách được cập nhật.
2. Cập nhật thất bại: Dữ liệu cũ được giữ nguyên.

Dòng sự kiện chính
1. Người dùng chọn "Quản lý sách".
2. Hệ thống hiển thị trang sách.
3. Người dùng chọn sách cần cập nhật.
4. Hệ thống lấy thông tin sách từ cơ sở dữ liệu.
5. Hệ thống hiển thị thông tin chi tiết sách cùng form sửa.
6. Người dùng chỉnh sửa các thông tin của sách.
7. Hệ thống kiểm tra tính hợp lệ của dữ liệu.
8. Hệ thống cập nhật thông tin vào cơ sở dữ liệu.
9. Hệ thống hiển thị thông báo "Cập nhật thành công".
10. Hoàn tất thao tác.

Luồng sự kiện phụ
Luồng phụ A: Hủy thao tác
1. B5, B6.a: Người dùng nhấn "Hủy".
2. B5, B6.b: Hệ thống không lưu thay đổi và quay lại màn hình "Trang sách".

Luồng phụ B: Thiếu thông tin bắt buộc
1. B7.a: Hệ thống hiển thị "Vui lòng nhập đầy đủ thông tin".
2. B7.b: Hệ thống quay lại bước 5.

Luồng phụ C: Sai định dạng dữ liệu
1. B7.a: Hệ thống hiển thị "Dữ liệu không hợp lệ".
2. B7.b: Hệ thống quay lại bước 5.

Luồng phụ D: Năm xuất bản không hợp lệ
1. B7.a: Hệ thống hiển thị "Năm xuất bản không hợp lệ".
2. B7.b: Hệ thống quay lại bước 5.

Luồng phụ E: Số lượng không hợp lệ
1. B7.a: Hệ thống hiển thị "Số lượng phải là số nguyên dương".
2. B7.b: Hệ thống quay lại bước 5.

Luồng phụ G: Lỗi hệ thống khi cập nhật
1. B8.a: Hệ thống hiển thị "Cập nhật thất bại, vui lòng thử lại".
2. B8.b: Hệ thống giữ nguyên dữ liệu cũ.

Yêu cầu đặc biệt
1. Chỉ người dùng có quyền (Quản lý thư viện, Thủ thư) mới được phép thực hiện chức năng cập nhật sách.
2. Mã sách là duy nhất và không được phép chỉnh sửa.
3. Tên sách không được để trống.
4. Nhà xuất bản không được để trống.
5. Thể loại phải tồn tại trong hệ thống.
6. Năm xuất bản phải là số hợp lệ và không lớn hơn năm hiện tại.
7. Số lượng phải là số nguyên dương.
8. Ngôn ngữ không được để trống.
9. Vị trí không được để trống.
10. Hệ thống phải kiểm tra tính hợp lệ của toàn bộ dữ liệu trước khi cập nhật.
11. Hệ thống phải đảm bảo tính toàn vẹn dữ liệu (không cập nhật nếu có lỗi).
12. Thời gian xử lý yêu cầu cập nhật không vượt quá 3 giây trong điều kiện bình thường.
13. Hệ thống phải hiển thị thông báo rõ ràng cho người dùng trong mọi trường hợp.

Ánh xạ triển khai
1. Web:
   - GET /sach/{book}/edit (màn hình cập nhật sách)
   - PUT /sach/{book} (cập nhật thông tin sách)
2. API:
   - PUT /api/books/{book} (cập nhật thông tin sách)
