Usecase ID: UC - DN - 01

TÊN USECASE
ĐĂNG NHẬP

Mô tả ngắn gọn
Người dùng (Quản lý, Thủ thư) nhập thông tin tài khoản hợp lệ để truy cập và sử dụng các chức năng của hệ thống thư viện theo phân quyền.

Tác nhân chính
Người dùng (Quản lý thư viện, Thủ thư)

Tiền điều kiện
Người dùng đã có tài khoản được cấp.

Hậu điều kiện
Đăng nhập thành công: Hệ thống tạo token đăng nhập (API) hoặc phiên làm việc (Web), gán phân quyền và chuyển vào màn hình phù hợp.
Đăng nhập thất bại: Hệ thống báo lỗi, người dùng ở lại màn hình đăng nhập.

Dòng sự kiện chính
1. Người dùng truy cập màn hình đăng nhập.
2. Hệ thống hiển thị form đăng nhập.
3. Người dùng nhập Tên đăng nhập và Mật khẩu.
4. Người dùng nhấn Đăng nhập.
5. Hệ thống kiểm tra hợp lệ dữ liệu đầu vào (không để trống).
6. Hệ thống kiểm tra giới hạn đăng nhập sai theo IP + tên đăng nhập.
7. Hệ thống truy vấn CSDL đối chiếu tài khoản.
8. Hệ thống kiểm tra trạng thái tài khoản: Đang hoạt động, Bị khóa, Chờ kích hoạt.
9. Hệ thống đối chiếu mật khẩu bằng cơ chế hash.
10. Hệ thống ghi nhận phiên đăng nhập hoặc phát hành Bearer token.
11. Hệ thống gán quyền truy cập theo vai trò.
12. Hoàn tất thao tác.

Luồng sự kiện phụ
Luồng phụ A: Để trống thông tin
A1. Nếu thiếu Tên đăng nhập hoặc Mật khẩu, hiển thị "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu".
A2. Giữ nguyên màn hình đăng nhập.

Luồng phụ B: Sai tên đăng nhập hoặc mật khẩu
B1. Hiển thị "Tên đăng nhập hoặc mật khẩu không chính xác".
B2. Không giữ mật khẩu đã nhập.
B3. Tăng bộ đếm đăng nhập sai cho cơ chế chống brute-force.

Luồng phụ C: Tài khoản bị khóa hoặc chờ kích hoạt
C1. Nếu trạng thái là Bị khóa, hiển thị "Tài khoản của bạn đã bị khóa. Vui lòng liên hệ thư viện để biết thêm chi tiết".
C2. Nếu trạng thái là Chờ kích hoạt, hiển thị "Tài khoản đang chờ kích hoạt".
C3. Từ chối đăng nhập.

Luồng phụ D: Quá nhiều lần đăng nhập sai
D1. Khi vượt ngưỡng cho phép, hiển thị yêu cầu thử lại sau và cờ captcha_required.
D2. Từ chối đăng nhập cho đến khi hết thời gian khóa tạm.

Luồng phụ E: Lỗi kết nối hệ thống
E1. Nếu lỗi kết nối CSDL hoặc máy chủ, hiển thị "Lỗi kết nối hệ thống. Vui lòng thử lại sau".
E2. Hủy thao tác đăng nhập.

Yêu cầu đặc biệt
1. Mật khẩu được đối chiếu bằng Hash, không so sánh plain text.
2. Hỗ trợ Ghi nhớ đăng nhập qua remember token (Web) và thời hạn token dài hơn khi remember_me=true (API).
3. Có cơ chế chống spam/brute-force bằng giới hạn số lần đăng nhập sai.
4. Mục tiêu thời gian phản hồi dưới 3 giây trong điều kiện vận hành bình thường.

Ánh xạ triển khai
- Web login: POST /login
- API login: POST /api/auth/login
- API me: GET /api/auth/me
- API logout: POST /api/auth/logout
