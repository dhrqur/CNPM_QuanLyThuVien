Usecase ID: UC - S - 04

TÊN USECASE
TÌM KIẾM SÁCH

Mô tả ngắn gọn
Người dùng tìm kiếm thông tin sách trong hệ thống theo các tiêu chí.

Tác nhân chính
Quản lý thư viện, Thủ thư

Tiền điều kiện
1. Người dùng đã đăng nhập vào hệ thống.
2. Người dùng có quyền truy cập chức năng quản lý sách.
3. Hệ thống đã có dữ liệu sách.

Hậu điều kiện
1. Tìm kiếm thành công: Hệ thống hiển thị danh sách sách phù hợp với tiêu chí tìm kiếm.
2. Tìm kiếm thất bại: Hệ thống thông báo không tìm thấy dữ liệu hoặc lỗi tìm kiếm.

Dòng sự kiện chính
1. Người dùng chọn "Quản lý sách".
2. Hệ thống hiển thị trang sách và ô tìm kiếm.
3. Người dùng nhập nội dung cần tìm vào ô tìm kiếm.
4. Hệ thống kiểm tra dữ liệu tìm kiếm.
5. Hệ thống thực hiện tìm kiếm trong cơ sở dữ liệu.
6. Hệ thống hiển thị danh sách kết quả tìm kiếm.
7. Hoàn tất thao tác.

Luồng sự kiện phụ
Luồng phụ A: Không nhập tiêu chí tìm kiếm
1. B5.a: Nếu không nhập tiêu chí, hệ thống hiển thị toàn bộ danh sách sách.
2. B5.b: Quay lại màn hình kết quả.

Luồng phụ B: Không tìm thấy kết quả
1. B6.a: Hệ thống hiển thị "Không tìm thấy sách phù hợp".
2. B6.b: Hệ thống quay lại màn hình tìm kiếm.

Luồng phụ C: Lỗi hệ thống khi tìm kiếm
1. B6.a: Hệ thống hiển thị "Tìm kiếm thất bại, vui lòng thử lại".
2. B6.b: Hủy thao tác tìm kiếm.

Yêu cầu đặc biệt
1. Chỉ người dùng có quyền (Quản lý thư viện, Thủ thư) mới được phép thực hiện chức năng tìm kiếm sách.
2. Hệ thống phải cho phép tìm kiếm theo một hoặc nhiều tiêu chí.
3. Các tiêu chí tìm kiếm bao gồm: Mã sách, Tên sách, Tác giả, Trạng thái sách (Còn hoặc Hết).
4. Hệ thống phải hỗ trợ tìm kiếm gần đúng đối với tên sách và tác giả.
5. Hệ thống phải hiển thị danh sách kết quả rõ ràng, dễ theo dõi.
6. Hệ thống phải kiểm tra tính hợp lệ của dữ liệu nhập trước khi thực hiện tìm kiếm.
7. Thời gian xử lý yêu cầu tìm kiếm không vượt quá 3 giây trong điều kiện bình thường.
8. Hệ thống phải hiển thị thông báo rõ ràng cho người dùng trong mọi trường hợp.

Ánh xạ triển khai
1. Web:
   - GET /sach?search=&maSach=&tenSach=&tacGia=&trangThai=
2. API:
   - GET /api/books?search=&maSach=&tenSach=&tacGia=&trangThai=
