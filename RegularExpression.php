<?php

/*******************************************************
 * REGEX ALL-IN-ONE (PHP PCRE)
 * Tác giả: Thành & Tĩnh
 * Mục tiêu: Trình diễn đầy đủ RegEx trong PHP, có output
 *******************************************************/
header('Content-Type: text/html; charset=UTF-8');

// ===== UI helpers =====
function h1($t)
{
              echo "<h1 style='font-family:system-ui;margin:16px 0;'>$t</h1>";
}
function h2($t)
{
              echo "<h2 style='font-family:system-ui;margin:12px 0;color:#374151;'>$t</h2>";
}
function p($t)
{
              echo "<p style='font-family:system-ui;line-height:1.6;margin:6px 0;'>$t</p>";
}
function pre($t)
{
              echo "<pre style='background:#0b1220;color:#e5e7eb;padding:12px;border-radius:10px;overflow:auto;'>$t</pre>";
}
function sep()
{
              echo "<hr style='margin:20px 0;border:0;border-top:1px solid #ddd;'>";
}
function dump_match($matches)
{
              echo "<pre style='background:#0f172a;color:#d1d5db;padding:10px;border-radius:8px;overflow:auto;'>";
              print_r($matches);
              echo "</pre>";
}

echo "<div style='max-width:1000px;margin:24px auto;padding:0 12px'>";

h1("REGULAR EXPRESSIONS (Biểu thức chính quy) – PHP PCRE");

// Link hữu ích
p('Công cụ kiểm thử online: <a href="https://regex101.com/" target="_blank">regex101.com</a> (chọn PCRE).');

// =====================================================
// 1) Cú pháp cơ bản: Delimiter, Ký tự đặc biệt, Mốc neo, Nhóm, Toán tử
// =====================================================
sep();
h2("1) Cú pháp & khái niệm cơ bản");

p("<b>Delimiter</b>: dấu phân cách pattern. Ví dụ: <code>/pattern/</code>, <code>#pattern#</code>, <code>~pattern~</code>. Chọn delimiter giúp ít phải escape.");
p("<b>Ký tự đặc biệt</b>: <code>.</code> (bất kỳ ký tự), <code>\\d</code> (số), <code>\\w</code> (chữ/số/_), <code>\\s</code> (khoảng trắng), v.v.");
p("<b>Mốc neo</b>: <code>^</code> đầu chuỗi, <code>$</code> cuối chuỗi, <code>\\b</code> ranh giới từ.");
p("<b>Nhóm</b>: <code>(...)</code> bắt giữ, <code>(?:...)</code> không bắt giữ.");
p("<b>Toán tử</b>: <code>|</code> (hoặc), <code>*</code> (0+), <code>+</code> (1+), <code>?</code> (0/1), <code>{m,n}</code> (lặp) – thêm <i>?</i> để non-greedy.");

$subject = "Email: a.b-c+1@example.com và a@example.co, số: 0935-123-456.";
$pattern = '/\b[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}\b/i';
preg_match_all($pattern, $subject, $mails);
p("Ví dụ tìm email trong chuỗi:");
pre('$pattern = \'/\b[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}\b/i\';');
pre('$subject = "Email: a.b-c+1@example.com và a@example.co, số: 0935-123-456."');
dump_match($mails);

// =====================================================
// 2) Modifier (cờ): i, m, s, u, x, U
// =====================================================
sep();
h2("2) Modifier (cờ)");

p("<code>i</code>: không phân biệt hoa/thường; <code>m</code>: ^ và $ áp dụng theo từng dòng; <code>s</code>: . khớp cả xuống dòng; <code>u</code>: Unicode; <code>x</code>: cho phép khoảng trắng/chú thích trong pattern; <code>U</code>: đảo ngược greedy->ungreedy mặc định.");
$subject = "Line1\nLine2";
$pattern_m = '/^Line\d$/m'; // ^ và $ theo từng dòng
preg_match_all($pattern_m, $subject, $lines);
p("Ví dụ <code>m</code> (multiline):");
pre('$pattern = \'/^Line\d$/m\'; $subject = "Line1\nLine2";');
dump_match($lines);

$pattern_s = '/A.*Z/s';
$subject_s = "A  \n  Z";
preg_match($pattern_s, $subject_s, $ms);
p("Ví dụ <code>s</code> (dotall): <code>.</code> khớp cả xuống dòng");
pre('$pattern = \'/A.*Z/s\'; $subject = "A  \n  Z";');
dump_match($ms);

// =====================================================
// 3) preg_match vs preg_match_all
// =====================================================
sep();
h2("3) preg_match vs preg_match_all");

$subject = "abcdaaa";
$pattern = '/a/';
preg_match($pattern, $subject, $one);
preg_match_all($pattern, $subject, $all);
pre('preg_match("/a/", "abcdaaa", $one); // lần khớp đầu tiên');
dump_match($one);
pre('preg_match_all("/a/", "abcdaaa", $all); // tất cả khớp');
dump_match($all);

// =====================================================
// 4) Nhóm (Capturing), Tham chiếu ngược (Backreferences), Nhóm đặt tên
// =====================================================
sep();
h2("4) Nhóm, Backreferences, Named Captures");

$subject = "2025-09-28 và 1999-12-31";
$pattern = '/(\d{4})-(\d{2})-(\d{2})/';
preg_match_all($pattern, $subject, $dates);
p("Nhóm bắt giữ (1)(2)(3): Năm-Tháng-Ngày");
pre('/(\d{4})-(\d{2})-(\d{2})/');
dump_match($dates);

$subject = "haha hehe hihi";
$pattern = '/\b(\w)\1\b/'; // từ có 2 kí tự giống nhau lặp 2 lần (ha-ha không khớp, "aa" sẽ khớp)
preg_match_all($pattern, $subject, $doubles);
p("Tham chiếu ngược: <code>\\1</code> tham chiếu nhóm 1");
pre('/\b(\w)\1\b/  // từ có 2 ký tự giống nhau');
dump_match($doubles);

$subject = "Name: Thanh, Age: 21";
$pattern = '/Name:\s*(?P<name>\w+),\s*Age:\s*(?P<age>\d+)/';
preg_match($pattern, $subject, $named);
p("Nhóm đặt tên: <code>(?P<name>...)</code>");
pre('/Name:\s*(?P<name>\w+),\s*Age:\s*(?P<age>\d+)/');
dump_match($named);

// =====================================================
// 5) Lookaround: Lookahead & Lookbehind (khẳng định/loại trừ)
// =====================================================
sep();
h2("5) Lookaround (Lookahead/Lookbehind)");

$subject = "price: 100USD, 200VND, 300USD";
$pattern = '/\d+(?=USD)/'; // dương tính: theo sau là USD
preg_match_all($pattern, $subject, $usd);
p("Positive lookahead <code>(?=...)</code>: số đứng trước 'USD'");
dump_match($usd);

$pattern = '/(?<=price:\s)\d+/'; // dương tính: đứng sau 'price: '
preg_match($pattern, $subject, $afterPrice);
p("Positive lookbehind <code>(?<=...)</code>: số ngay sau 'price: '");
dump_match($afterPrice);

$pattern = '/\d+(?!USD)/'; // phủ định: số KHÔNG theo sau USD
preg_match_all($pattern, $subject, $notUsd);
p("Negative lookahead <code>(?!...)</code>: số không theo sau 'USD'");
dump_match($notUsd);

// =====================================================
// 6) Unicode & lớp ký tự có thuộc tính: \p{L}, \p{N}, ...
// =====================================================
sep();
h2("6) Unicode (modifier u) & \\p{...}");

$subject = "Tiếng Việt có dấu: Đặng, Trần, Nguyễn, café 123";
$pattern = '/\p{L}+/u'; // từ gồm chữ cái Unicode
preg_match_all($pattern, $subject, $words);
p("Tìm các cụm chữ Unicode bằng <code>\\p{L}+</code> với cờ <code>u</code>:");
dump_match($words);

// =====================================================
// 7) Tách chuỗi: preg_split
// =====================================================
sep();
h2("7) Tách chuỗi với preg_split");

$subject = "one, two; three | four   five";
$pattern = '/[\s,;|]+/'; // tách theo khoảng trắng hoặc , ; |
$parts = preg_split($pattern, $subject);
pre('$parts = preg_split("/[\s,;|]+/", "one, two; three | four   five");');
dump_match($parts);

// =====================================================
// 8) Thay thế: preg_replace & preg_replace_callback (sanitize/format)
// =====================================================
sep();
h2("8) Thay thế: preg_replace & preg_replace_callback");

// 8.1 chuyển dd/mm/yyyy -> yyyy-mm-dd
$subject = "Hạn: 28/09/2025, Sinh: 06/02/2004";
$pattern = '/(\d{2})\/(\d{2})\/(\d{4})/';
$repl    = '$3-$2-$1';
$converted = preg_replace($pattern, $repl, $subject);
p("Đổi định dạng ngày <code>dd/mm/yyyy</code> → <code>yyyy-mm-dd</code>:");
pre('$converted = preg_replace("/(\d{2})\/(\d{2})\/(\d{4})/", "$3-$2-$1", $subject);');
p("Kết quả: <b>$converted</b>");

// 8.2 sanitize username: chỉ giữ chữ/số/_
$subject = "thanh!@#$$ học_PHP-8";
$pattern = '/[^\w]+/u';
$san = preg_replace($pattern, '_', $subject);
p("Sanitize username (chỉ chữ/số/_):");
pre('preg_replace("/[^\w]+/u", "_", "thanh!@#$$ học_PHP-8")');
p("Kết quả: <b>$san</b>");

// 8.3 callback: bọc URL thành thẻ <a>
$subject = "Tham khảo https://example.com và http://duytan.edu.vn";
$pattern = '~\bhttps?://[^\s<]+~i';
$linked = preg_replace_callback($pattern, function ($m) {
              $url = htmlspecialchars($m[0], ENT_QUOTES, 'UTF-8');
              return '<a href="' . $url . '" target="_blank">' . $url . '</a>';
}, $subject);
p("Biến URL thành link bằng <code>preg_replace_callback</code>:");
pre($linked);

// =====================================================
// 9) Ứng dụng thực tế: Validate & Extract
// =====================================================
sep();
h2("9) Ứng dụng thực tế (Validate & Extract)");

// 9.1 Email (đơn giản, thực dụng)
$email = "a.b+1@example.co.vn";
$re = '/^[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$/i';
p("<b>Email</b>: " . htmlspecialchars($email) . " → " . (preg_match($re, $email) ? "Hợp lệ" : "Không hợp lệ"));

// 9.2 SĐT Việt Nam (phổ biến): 0xxx-xxxxxx hoặc +84...
$phone = "+84935-123-456";
$rePhone = '/^(?:\+?84|0)(?:\d[\s\-]?){8,9}\d$/';
p("<b>SĐT VN</b>: " . htmlspecialchars($phone) . " → " . (preg_match($rePhone, $phone) ? "Hợp lệ" : "Không hợp lệ"));

// 9.3 Mật khẩu: >=8, có chữ hoa, thường, số, ký tự đặc biệt
$pwd = "Abc@1234";
$rePwd = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/';
p("<b>Password</b>: " . htmlspecialchars($pwd) . " → " . (preg_match($rePwd, $pwd) ? "Mạnh" : "Yếu"));

// 9.4 Trích xuất thẻ HTML (minh hoạ – không khuyến nghị parse HTML bằng regex)
$html = "<p>Hello <b>World</b></p>";
$reTag = '/<([a-z]+)(?:\s[^>]*)?>(.*?)<\/\1>/i';
preg_match_all($reTag, $html, $tags);
p("Trích xuất cặp thẻ HTML (demo):");
dump_match($tags);

// =====================================================
// 10) Các lưu ý quan trọng (Best Practices)
// =====================================================
sep();
h2("10) Best Practices & Lỗi thường gặp");
echo "<ul style='font-family:system-ui;line-height:1.6'>
  <li>Chọn <b>delimiter</b> phù hợp để giảm escape (vd: <code>#</code> khi có nhiều <code>/</code> trong pattern).</li>
  <li>Dùng <b>modifier</b> đúng ngữ cảnh: <code>i</code> (case-insensitive), <code>u</code> (Unicode), <code>m</code> (multi-line), <code>s</code> (dotall).</li>
  <li>Ưu tiên <b>nhóm không bắt giữ</b> <code>(?:...)</code> nếu không cần lưu kết quả để tăng tốc độ.</li>
  <li>Kiểm soát <b>greedy vs non-greedy</b>: thêm <code>?</code> sau lượng từ (<code>*?</code>, <code>+?</code>, <code>{m,n}?</code>).</li>
  <li>Với Unicode (tiếng Việt có dấu), nhớ bật <code>u</code> và cân nhắc <code>\\p{L}</code>, <code>\\X</code>.</li>
  <li>Parsing HTML/XML phức tạp → dùng parser chuyên dụng (DOM, SimpleXML), regex chỉ để thao tác nhỏ.</li>
  <li>Luôn <b>escape</b> đầu vào khi đưa vào HTML (<code>htmlspecialchars</code>) để tránh XSS.</li>
  <li>Test pattern trên <a href='https://regex101.com/' target='_blank'>regex101</a> để debug và xem mô tả từng nhóm.</li>
</ul>";

sep();
p("<i>Kết thúc trang. Hãy chỉnh các biến <code>\$subject</code>, <code>\$pattern</code> từng ví dụ để tự luyện. Good luck!</i>");

echo "</div>";
