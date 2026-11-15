# Thư viện WordPress dùng chung

Phiên bản thư viện: **2.0.0**.

Nguồn chuẩn: `inc/shared/` trong child theme PCN (`Phongcachnhat`). CopiersVN và các website khác nhận bản sao từ PCN; mỗi website nạp bản local của mình, không require chéo đường dẫn sang website khác. Thư viện ban đầu được tách từ CopiersVN ở dòng phiên bản 1.x.

## Module và API

| File | Phiên bản | API | Kết quả |
| --- | --- | --- | --- |
| `breadcrumbs.php` | 2.0.0 | `ishen_shared_get_breadcrumb_html($args = array())` | Chuỗi HTML, không tự `echo` hoặc đăng ký vị trí hiển thị |
| `breadcrumbs.php` | 2.0.0 | `ishen_shared_get_breadcrumb_separator($class = 'divider')` | Chuỗi HTML dấu phân cách mặc định |
| `single-post-breadcrumb.php` | 2.0.0 | `ishen_shared_single_post_breadcrumb()` | `echo` breadcrumb bài viết, không nhận tham số |
| `post-category-breadcrumb.php` | 2.0.0 | `ishen_shared_get_post_cat_breadcrumb_html($term)` | Chuỗi HTML breadcrumb danh mục bài viết |

Các helper nội bộ về ngữ cảnh, term và item trong `breadcrumbs.php` cũng dùng `ishen_shared_`. WordPress là phụ thuộc nền tảng; Rank Math và WooCommerce chỉ dùng khi ngữ cảnh cần và API có sẵn.

## Bộ xử lý breadcrumb

```php
$breadcrumb_html = function_exists('ishen_shared_get_breadcrumb_html')
    ? ishen_shared_get_breadcrumb_html(array('context' => 'auto'))
    : '';

if ($breadcrumb_html !== '') {
    echo $breadcrumb_html;
}
```

| `context` | Nguồn dữ liệu |
| --- | --- |
| `auto` | Nhận biết ngữ cảnh; loại chưa hỗ trợ trả chuỗi rỗng |
| `single_post` | Rank Math nếu có; fallback Trang chủ, danh mục cha/con và tiêu đề bài |
| `post_category` | Rank Math nếu term khớp truy vấn; fallback tổ tiên và danh mục hiện tại |
| `author` | Rank Math nếu có; fallback Trang chủ và tên hiển thị tác giả |
| `single_product`, `product_category`, `shop` | `WC_Breadcrumb` native, giữ filter và thứ tự danh mục |
| `page` | Rank Math nếu có; fallback Trang chủ, trang cha và trang hiện tại |

- `use_rank_math => false` ép fallback. Truyền `term` khác truy vấn hiện tại cũng dùng fallback để không lấy nhầm breadcrumb Rank Math.
- `items` nhận mảng `array(array('Tên', 'URL'), array('Trang hiện tại', ''))`; khi truyền, bộ xử lý không gọi nguồn dữ liệu khác. Nhãn và URL được escape khi dựng HTML.
- Các tùy chọn `class`, `home_label`, `separator`, `wrap_before`, `wrap_after`, `before`, `after`, `current_before`, `current_after`, `show_current` điều khiển hiển thị. Các trường HTML là cấu hình tin cậy từ theme, không truyền dữ liệu request thô vào.
- Mặc định mục cuối dùng `<span>` không có liên kết; `show_current => false` ẩn mục cuối. Separator mặc định là icon Font Awesome Pro Light `fa-slash-forward`; website tự nạp thư viện icon và CSS.
- Dùng `items` hoặc cấu hình khi cần biến thể; không sao chép phần dựng HTML thành hàm mang thương hiệu khác.
- Thư viện không tự nạp CSS, đăng ký hook hiển thị hoặc tạo schema; adapter của website quyết định vị trí và giữ schema/hook của plugin SEO, WooCommerce và theme.

## Adapter và giao diện hiện hữu

- Class breadcrumb cũ như `cvn-single-breadcrumbs`, `cvn-single-breadcrumbs__divider`, `cvn-post-cat-breadcrumbs` giữ nguyên để tương thích CSS. Đổi prefix PHP không đổi class HTML.
- PCN dùng bộ xử lý chung tại `inc/blog/single-post-header.php`, truyền dấu `›` để giữ giao diện bài viết hiện tại; hai khai báo breadcrumb cũ tại file này đã được loại bỏ.
- CopiersVN dùng wrapper bài viết/danh mục, adapter tác giả và `woocommerce/global/breadcrumb.php`. Adapter WooCommerce giữ dữ liệu `$breadcrumb`, hook `flatsome_before_breadcrumb`/`flatsome_after_breadcrumb`, mục hiện tại và fallback về parent khi thiếu thư viện.
- CSS, màu sắc và kiểu chữ vẫn do mỗi dự án quản lý. Không copy CSS hoặc adapter của một dự án sang dự án khác theo mặc định.

## Tích hợp và nâng cấp

1. Tìm khai báo, hook và nơi gọi API hiện có ở dự án đích. Đối chiếu và hợp nhất bản trùng trước khi nạp thư viện.
2. Copy các module PHP, bootstrap và `README.md` của `inc/shared/` từ PCN. Nếu dự án đã tham chiếu bộ quy tắc PCN, đọc quy tắc thư viện tại nguồn và không duy trì bản sao `inc/shared/AGENTS.md` local; nếu không truy cập được PCN, mang theo file quy tắc và ghi rõ nguồn. Nạp `inc/shared/bootstrap.php` qua danh sách bootstrap cấp nhóm của child theme, trước các module sử dụng nó.
3. Ở nơi hiển thị, kiểm tra `function_exists()` và chỉ xuất wrapper khi HTML không rỗng. Phần độc lập vẫn hiển thị khi thiếu module.
4. Khi nâng từ 1.x, thay tiền tố của bảy hàm breadcrumb `cvn_` bằng `ishen_shared_`, cập nhật mọi nơi gọi và xóa khai báo cũ đã được thay thế. Không tạo alias. Giữ nguyên kiểu trả về/`echo` và class HTML. Việc nâng cấp này là thay đổi API có chủ đích ở phiên bản lớn 2.0.0.
5. Đọc quy tắc thư viện theo bảng chỉ dẫn trong `AGENTS.md` gốc PCN. `AGENTS.md` dự án đích chỉ tham chiếu bộ quy tắc PCN và ghi bổ sung/ngoại lệ riêng; không sao chép lại bộ quy tắc chung.
6. PHP lint các module, loader và nơi gọi; kiểm tra đầu ra Rank Math/fallback, WooCommerce nếu có tích hợp và tình huống thiếu module. Đối chiếu breadcrumb trước/sau trên các trang thực tế.

## Trạng thái tích hợp

| Dự án | Mã thư viện | Phạm vi | Kiểm tra |
| --- | --- | --- | --- |
| PCN | 2.0.0, nguồn chuẩn | Bootstrap và breadcrumb header bài viết | Xem kết quả kiểm tra bên dưới |
| CopiersVN | 2.0.0, bản sao PCN | Bài viết, danh mục, tác giả và adapter WooCommerce | Xem kết quả kiểm tra bên dưới |
| Viện Giáp Vú Gan | 2.0.0, bản sao PCN | Breadcrumb bài viết qua `inc/blog/single-post.php` | PHP lint đạt; trang bài viết có một breadcrumb, kiểm tra 360/390/849/850/1440px không tràn ngang; trang danh mục vẫn trả HTTP 200 |

Copy source không tự đồng bộ các dự án và không thay thế kiểm tra runtime.

## Kiểm tra bản 2.0.0

- PHP 8.2.29: lint các module, loader và nơi gọi liên quan đều đạt.
- Bộ đối chiếu tạm trước/sau: 131 kiểm tra đạt qua bốn chế độ native, Rank Math, WooCommerce và thiếu module. Bao gồm ngữ cảnh tự nhận diện, cấu hình tùy chọn, wrapper và việc giữ nguyên đầu ra header PCN. Các fixture cũ chỉ phục vụ đối chiếu và được dọn sau khi kiểm tra.
- CopiersVN Local: bài viết, danh mục bài viết, tác giả và Shop đều trả HTTP 200; HTML breadcrumb khớp bản trước di chuyển ở cả bốn trang.
- PCN Local: sau khi người dùng bật site, trang chủ và bài viết trả HTTP 200; bài viết có đúng một breadcrumb, giữ dấu `›` và tiêu đề H1. Site chưa chạy ở thời điểm lấy baseline, nên đối chiếu trước/sau header PCN dựa trên bộ kiểm tra PHP; kiểm tra HTTP xác nhận tích hợp WordPress thực tế sau thay đổi.

## Cập nhật thư viện

- Sửa bản chuẩn tại PCN, cập nhật version/changelog, đối chiếu bản local và chỉ đồng bộ dự án thuộc phạm vi yêu cầu.
- Giữ API ổn định sau lần chuyển 2.0.0; nếu cần biến thể, bổ sung cấu hình tương thích. Không đổi prefix theo thương hiệu đích.
- Khi báo hoàn thành, ghi rõ dự án đã đồng bộ, bước đã chạy và giới hạn còn lại; dọn phần cũ được thay thế trong cùng task.

## Lịch sử

- **2.0.0**: Chuyển nguồn chuẩn từ CopiersVN về PCN, chuẩn hóa bảy hàm breadcrumb sang tiền tố `ishen_shared_`; cập nhật nơi gọi ở CopiersVN, tích hợp PCN và xóa hai khai báo breadcrumb cũ trong header PCN. Giữ thuật toán và class HTML của thư viện 1.1.4, không thêm alias cũ.
- **1.1.4**: Bổ sung ngữ cảnh tác giả, ưu tiên Rank Math và fallback bằng tên tác giả; không tự tạo schema thứ hai.
- **1.1.0–1.1.3**: Thêm bộ xử lý nhiều ngữ cảnh, adapter WooCommerce, dấu phân cách chung và tách CSS riêng dự án.
- **1.0.0–1.0.1**: Tách module breadcrumb bài viết tại CopiersVN, bổ sung bootstrap và tài liệu tích hợp.
