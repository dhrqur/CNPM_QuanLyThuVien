Usecase ID: UC - S - 05

TÊN USECASE
TỰ ĐỘNG ĐIỀU CHỈNH TRẠNG THÁI SÁCH

Mô tả ngắn gọn
Hệ thống tự động cập nhật trạng thái sách dựa trên số lượng sách trong kho.

Tác nhân chính
Hệ thống

Tiền điều kiện
1. Sách tồn tại trong hệ thống.
2. Có thay đổi về số lượng sách.

Hậu điều kiện
1. Nếu số lượng bằng 0 thì trạng thái sách là "Hết".
2. Nếu số lượng lớn hơn 0 thì trạng thái sách là "Còn".

Dòng sự kiện chính
1. Có sự thay đổi số lượng sách (do thêm, cập nhật, mượn, trả sách).
2. Hệ thống nhận sự thay đổi số lượng.
3. Hệ thống kiểm tra số lượng sách hiện tại.
4. Nếu số lượng sách bằng 0 thì cập nhật trạng thái thành "Hết".
5. Nếu số lượng sách lớn hơn 0 thì cập nhật trạng thái thành "Còn".
6. Hệ thống lưu trạng thái mới vào cơ sở dữ liệu.
7. Hoàn tất thao tác.

Luồng sự kiện phụ
Luồng phụ A: Không có thay đổi số lượng
1. A1: Hệ thống không thực hiện cập nhật trạng thái.

Luồng phụ B: Lỗi hệ thống
1. B1: Hệ thống hiển thị "Không thể cập nhật trạng thái sách" và hủy thao tác hiện tại.

Yêu cầu đặc biệt
1. Việc cập nhật trạng thái phải được thực hiện tự động, không cần người dùng thao tác.
2. Dữ liệu phải được cập nhật ngay sau khi số lượng thay đổi.
3. Trạng thái sách chỉ có hai giá trị: "Còn" hoặc "Hết".
4. Hệ thống phải đảm bảo tính chính xác và đồng bộ dữ liệu.
5. Thời gian xử lý phải nhanh, không gây ảnh hưởng đến các chức năng khác.

Ánh xạ triển khai
1. Tầng model:
   - App\Models\Book::booted() tự động đồng bộ trường trangThai khi tạo mới hoặc khi soLuong thay đổi.
2. Luồng phát sinh thay đổi số lượng:
   - Thêm/cập nhật sách (BookController, BookApiController).
   - Mượn/trả sách (BorrowService).
