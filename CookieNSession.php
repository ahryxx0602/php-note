<?php

declare(strict_types=1);

// ---------- cấu hình & helpers ----------
mb_internal_encoding('UTF-8');
header('Content-Type: text/html; charset=UTF-8');
ob_start();                 // để tránh "headers already sent" (demo setcookie/redirect)
session_name('SID_DEMO');   // đặt tên session (tùy chọn)
session_start();

function is_https(): bool
{
  return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
}
function set_cookie_demo(string $name, string $value, int $ttlSeconds = 3600, string $path = '/', ?string $domain = null, string $sameSite = 'Lax'): bool
{
  // PHP 7.3+: dùng mảng options, bật Secure/HttpOnly/SameSite
  $options = [
    'expires'  => time() + $ttlSeconds,
    'path'     => $path,
    'domain'   => $domain ?? '',       // để rỗng = mặc định host hiện tại
    'secure'   => is_https(),          // chỉ gửi cookie qua HTTPS nếu có
    'httponly' => true,                // JS không đọc được cookie (tăng bảo mật)
    'samesite' => $sameSite,           // Lax/Strict/None (None cần Secure=true)
  ];
  return setcookie($name, $value, $options);
}
function del_cookie_demo(string $name, string $path = '/', ?string $domain = null, string $sameSite = 'Lax'): bool
{
  $options = [
    'expires'  => time() - 3600,
    'path'     => $path,
    'domain'   => $domain ?? '',
    'secure'   => is_https(),
    'httponly' => true,
    'samesite' => $sameSite,
  ];
  return setcookie($name, '', $options);
}
function redirect(string $to): void
{
  header("Location: $to");
  exit;
}
function url_self(array $qs = []): string
{
  $base = strtok($_SERVER['REQUEST_URI'], '?');
  $merged = array_merge($_GET, $qs);
  return $base . (empty($merged) ? '' : ('?' . http_build_query($merged)));
}
function h(string $s): string
{
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// ---------- Flash message (session) ----------
function set_flash(string $key, string $message, string $type = 'info'): void
{
  $_SESSION['flash'][$key] = ['msg' => $message, 'type' => $type];
}
function get_flash(?string $key = null): array
{
  $out = [];
  if ($key === null) {
    $out = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
  } else {
    if (!empty($_SESSION['flash'][$key])) {
      $out[$key] = $_SESSION['flash'][$key];
      unset($_SESSION['flash'][$key]);
    }
  }
  return $out;
}

// ---------- CSRF helper ----------
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
function csrf_field(): string
{
  return '<input type="hidden" name="csrf" value="' . h($_SESSION['csrf']) . '">';
}
function check_csrf(): bool
{
  return isset($_POST['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

// ---------- DEMO ACTIONS (GET/POST) ----------
// Session counter
if (isset($_GET['action']) && $_GET['action'] === 'inc') {
  $_SESSION['counter'] = ($_SESSION['counter'] ?? 0) + 1;
  set_flash('counter', 'Đã tăng session counter!', 'success');
  redirect(url_self(['action' => null]));
}

// Set/Unset 1 biến session
if (isset($_GET['action']) && $_GET['action'] === 'set_user') {
  $_SESSION['user'] = ['name' => 'Thanh', 'role' => 'member'];
  set_flash('user', 'Đã set $_SESSION["user"].', 'success');
  redirect(url_self(['action' => null]));
}
if (isset($_GET['action']) && $_GET['action'] === 'unset_user') {
  unset($_SESSION['user']);
  set_flash('user', 'Đã unset $_SESSION["user"].', 'warning');
  redirect(url_self(['action' => null]));
}

// Destroy toàn bộ session (logout)
if (isset($_GET['action']) && $_GET['action'] === 'destroy') {
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
      'expires'  => time() - 3600,
      'path'     => $params['path'] ?? '/',
      'domain'   => $params['domain'] ?? '',
      'secure'   => $params['secure'] ?? is_https(),
      'httponly' => $params['httponly'] ?? true,
      'samesite' => $params['samesite'] ?? 'Lax',
    ]);
  }
  session_destroy();
  set_flash('session', 'Đã hủy toàn bộ session (logout).', 'danger');
  redirect(url_self(['action' => null]));
}

// Session encode/decode demo
if (isset($_GET['action']) && $_GET['action'] === 'encode') {
  $_SESSION['demo'] = ['x' => 1, 'y' => 2];
  $_SESSION['encoded_string'] = session_encode(); // Lưu chuỗi encode để hiển thị
  set_flash('encode', 'Đã session_encode() vào $_SESSION["encoded_string"].', 'success');
  redirect(url_self(['action' => null]));
}
if (isset($_GET['action']) && $_GET['action'] === 'decode') {
  if (!empty($_SESSION['encoded_string'])) {
    $str = $_SESSION['encoded_string'];
    // CẢNH BÁO: session_decode sẽ ghi đè $_SESSION hiện tại theo chuỗi truyền vào
    session_decode($str);
    set_flash('decode', 'Đã session_decode() từ chuỗi encode trước đó.', 'warning');
  } else {
    set_flash('decode', 'Không có chuỗi encode để decode.', 'danger');
  }
  redirect(url_self(['action' => null]));
}

// Cookie demo: set/del
if (isset($_GET['action']) && $_GET['action'] === 'set_cookie') {
  set_cookie_demo('demo_cookie', 'Xin chào Thành!', 600);
  set_flash('cookie', 'Đã tạo cookie demo_cookie (600s).', 'success');
  redirect(url_self(['action' => null]));
}
if (isset($_GET['action']) && $_GET['action'] === 'del_cookie') {
  del_cookie_demo('demo_cookie');
  set_flash('cookie', 'Đã xóa cookie demo_cookie.', 'warning');
  redirect(url_self(['action' => null]));
}

// Remember me (cookie)
if (isset($_GET['action']) && $_GET['action'] === 'remember') {
  $token = bin2hex(random_bytes(16));
  set_cookie_demo('remember_me', $token, 86400 * 7); // 7 ngày
  $_SESSION['remember_token_shadow'] = $token;     // demo: lưu để đối chiếu
  set_flash('remember', 'Đã set cookie remember_me (7 ngày).', 'success');
  redirect(url_self(['action' => null]));
}
if (isset($_GET['action']) && $_GET['action'] === 'forget') {
  del_cookie_demo('remember_me');
  unset($_SESSION['remember_token_shadow']);
  set_flash('remember', 'Đã xóa cookie remember_me.', 'warning');
  redirect(url_self(['action' => null]));
}

// Mini login demo (POST)
if (($_POST['action'] ?? '') === 'login') {
  if (!check_csrf()) {
    set_flash('login', 'CSRF token không hợp lệ.', 'danger');
    redirect(url_self());
  }
  $user = trim($_POST['username'] ?? '');
  $pass = trim($_POST['password'] ?? '');
  if ($user === 'admin' && $pass === '123') {
    $_SESSION['auth'] = ['user' => $user, 'time' => date('Y-m-d H:i:s')];
    set_flash('login', 'Đăng nhập thành công (session lưu trạng thái).', 'success');
  } else {
    set_flash('login', 'Sai thông tin đăng nhập.', 'danger');
  }
  redirect(url_self());
}
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
  unset($_SESSION['auth']);
  set_flash('login', 'Đã đăng xuất.', 'info');
  redirect(url_self(['action' => null]));
}

// ---------- UI ----------
function css(): void
{
  echo '<style>
  :root{ --bg:#0f172a; --fg:#e5e7eb; --muted:#94a3b8; --card:#111827; --ok:#16a34a; --warn:#f59e0b; --err:#ef4444; --info:#3b82f6; }
  body{ margin:0; background:#0b1020; color:var(--fg); font-family:system-ui,Segoe UI,Roboto,Helvetica,Arial; }
  .wrap{ max-width:1100px; margin:24px auto; padding:0 16px; }
  h1{ margin:12px 0 6px; }
  h2{ margin:18px 0 8px; color:#cbd5e1; }
  .row{ display:grid; grid-template-columns: repeat(auto-fit, minmax(320px,1fr)); gap:16px; }
  .card{ background:var(--card); border-radius:14px; padding:16px; box-shadow:0 0 0 1px #1f2937 inset; }
  .btn{ display:inline-block; margin:4px 6px 0 0; padding:8px 12px; border-radius:10px; background:#1f2937; color:#e5e7eb; text-decoration:none; border:1px solid #334155; }
  .btn:hover{ filter:brightness(1.1); }
  .ok{ background:#12381f; border-color:#1d4d2a; }
  .warn{ background:#3a2d12; border-color:#614a1d; }
  .err{ background:#3a1414; border-color:#5a1e1e; }
  .info{ background:#10243a; border-color:#1e3a5f; }
  pre, code{ background:#0b1220; color:#e5e7eb; padding:10px; border-radius:10px; overflow:auto; }
  table{ width:100%; border-collapse:collapse; }
  th, td{ text-align:left; padding:8px 10px; border-bottom:1px solid #1f2937; color:#d1d5db; }
  .muted{ color:var(--muted); font-size:14px; }
  .flash{ padding:10px 12px; border-radius:10px; margin:8px 0; }
  .flash.success{ background:#12381f; }
  .flash.warning{ background:#3a2d12; }
  .flash.danger{ background:#3a1414; }
  .flash.info{ background:#10243a; }
  input,button{ background:#0b1220; color:#e5e7eb; border:1px solid #374151; border-radius:10px; padding:8px 10px; }
  form{ display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
  small{ color:#a5b4fc }
  </style>';
}

?>
<!doctype html>
<html lang="vi">

<head>
  <meta charset="utf-8">
  <title>Cookie & Session – All in One</title>
  <?php css(); ?>
</head>

<body>
  <div class="wrap">
    <h1>COOKIE & SESSION – All in One</h1>
    <p class="muted">Trang này trình diễn: <b>Session</b> (server) & <b>Cookie</b> (client), các API thường dùng và best practices (HttpOnly, Secure, SameSite), flash message, remember-me, encode/decode, hủy từng phần/toàn phần, login demo + CSRF.</p>

    <?php foreach (get_flash() as $k => $f): ?>
      <div class="flash <?= h($f['type']) ?>"><b><?= h(strtoupper($f['type'])) ?>:</b> <?= h($f['msg']) ?></div>
    <?php endforeach; ?>

    <div class="row">
      <div class="card">
        <h2>1) Session (lưu trên <u>server</u>)</h2>
        <p>- Tạo/đọc/ghi bằng mảng <code>$_SESSION</code>, kết thúc trình duyệt → session thường mất (trừ khi cấu hình khác).</p>
        <a class="btn ok" href="<?= h(url_self(['action' => 'inc'])) ?>">Tăng session counter</a>
        <a class="btn info" href="<?= h(url_self(['action' => 'set_user'])) ?>">Set $_SESSION["user"]</a>
        <a class="btn warn" href="<?= h(url_self(['action' => 'unset_user'])) ?>">Unset $_SESSION["user"]</a>
        <a class="btn err" href="<?= h(url_self(['action' => 'destroy'])) ?>">session_destroy()</a>

        <h3>Session hiện tại</h3>
        <table>
          <tr>
            <th>Key</th>
            <th>Value</th>
          </tr>
          <?php foreach ($_SESSION as $k => $v): ?>
            <tr>
              <td><?= h((string)$k) ?></td>
              <td>
                <pre><?= h(var_export($v, true)) ?></pre>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
        <p class="muted">* Ghi nhớ: session id được lưu ở cookie phiên (tên <?= h(session_name()) ?>).</p>
      </div>

      <div class="card">
        <h2>2) Session Encode / Decode</h2>
        <p><code>session_encode()</code> chuyển toàn bộ <code>$_SESSION</code> thành chuỗi; <code>session_decode()</code> khôi phục từ chuỗi đó (và <b>ghi đè</b> $_SESSION).</p>
        <a class="btn ok" href="<?= h(url_self(['action' => 'encode'])) ?>">session_encode() → $_SESSION["encoded_string"]</a>
        <a class="btn warn" href="<?= h(url_self(['action' => 'decode'])) ?>">session_decode() từ encoded_string</a>

        <h3>$_SESSION["encoded_string"]</h3>
        <pre><?= h($_SESSION['encoded_string'] ?? '(chưa có)') ?></pre>
        <p class="muted">* Cẩn trọng khi dùng <code>session_decode</code> vì sẽ thay thế nội dung hiện tại.</p>
      </div>

      <div class="card">
        <h2>3) Cookie (lưu trên <u>client</u>)</h2>
        <p>- Trình duyệt tự gửi cookie lên server mỗi request. Hết hạn → bị xóa. Kích thước ~4KB/cookie.</p>
        <a class="btn ok" href="<?= h(url_self(['action' => 'set_cookie'])) ?>">Set cookie demo_cookie</a>
        <a class="btn warn" href="<?= h(url_self(['action' => 'del_cookie'])) ?>">Xóa cookie demo_cookie</a>

        <h3>Cookie hiện tại</h3>
        <table>
          <tr>
            <th>Name</th>
            <th>Value</th>
          </tr>
          <?php foreach ($_COOKIE as $k => $v): ?>
            <tr>
              <td><?= h((string)$k) ?></td>
              <td>
                <pre><?= h(var_export($v, true)) ?></pre>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
        <p class="muted">* Nên thiết lập <code>HttpOnly</code>, <code>Secure</code>, <code>SameSite</code> phù hợp để tăng bảo mật.</p>
      </div>

      <div class="card">
        <h2>4) Remember Me (cookie)</h2>
        <p>Demo tạo cookie <code>remember_me</code> 7 ngày (HttpOnly, Secure nếu HTTPS, SameSite=Lax) + lưu token đối chiếu trong session.</p>
        <a class="btn ok" href="<?= h(url_self(['action' => 'remember'])) ?>">Set remember_me (7 ngày)</a>
        <a class="btn warn" href="<?= h(url_self(['action' => 'forget'])) ?>">Xóa remember_me</a>
        <table>
          <tr>
            <th>Cookie remember_me</th>
            <td>
              <pre><?= h($_COOKIE['remember_me'] ?? '(chưa có)') ?></pre>
            </td>
          </tr>
          <tr>
            <th>Session shadow token</th>
            <td>
              <pre><?= h($_SESSION['remember_token_shadow'] ?? '(chưa có)') ?></pre>
            </td>
          </tr>
        </table>
        <p class="muted">* Thực tế: lưu token băm (hash) trong DB, ràng buộc user/expiry/device.</p>
      </div>

      <div class="card">
        <h2>5) Mini Login (Session) + CSRF</h2>
        <?php if (empty($_SESSION['auth'])): ?>
          <form method="post" action="<?= h(url_self()) ?>">
            <input type="hidden" name="action" value="login">
            <?= csrf_field() ?>
            <input name="username" placeholder="admin">
            <input name="password" placeholder="123" type="password">
            <button class="btn ok" type="submit">Login</button>
          </form>
          <p class="muted">Demo: <code>admin / 123</code>. Sau khi login, trạng thái được giữ bằng session.</p>
        <?php else: ?>
          <p>Xin chào, <b><?= h($_SESSION['auth']['user']) ?></b>. Đăng nhập lúc <?= h($_SESSION['auth']['time']) ?></p>
          <a class="btn err" href="<?= h(url_self(['action' => 'logout'])) ?>">Logout</a>
        <?php endif; ?>
      </div>

      <div class="card">
        <h2>6) So sánh nhanh & Ghi chú</h2>
        <table>
          <tr>
            <th>Tiêu chí</th>
            <th>Session</th>
            <th>Cookie</th>
          </tr>
          <tr>
            <td>Nơi lưu</td>
            <td>Server</td>
            <td>Client (trình duyệt)</td>
          </tr>
          <tr>
            <td>Bảo mật</td>
            <td>Tốt hơn (server-side)</td>
            <td>Phụ thuộc client; dùng HttpOnly/Secure/SameSite</td>
          </tr>
          <tr>
            <td>Dung lượng</td>
            <td>Lớn (server)</td>
            <td>~4KB/cookie</td>
          </tr>
          <tr>
            <td>Thời gian sống</td>
            <td>Hết phiên/tuỳ cấu hình</td>
            <td>Đến khi hết hạn (expires/max-age)</td>
          </tr>
          <tr>
            <td>Use case</td>
            <td>Auth state, giỏ hàng, flash</td>
            <td>Remember me, pref, A/B flag</td>
          </tr>
        </table>
        <p class="muted">
          Best practices:
          <br>• Với cookie: luôn cân nhắc <code>httponly</code>, <code>secure</code>, <code>samesite</code>.
          <br>• Không lưu thông tin nhạy cảm trực tiếp trong cookie (dùng token/băm).
          <br>• Dùng <code>session_regenerate_id(true)</code> sau đăng nhập để chống fixation.
          <br>• Flash message: lưu vào session rồi đọc & xóa ngay sau khi hiển thị.
        </p>
      </div>
    </div>

    <h2>7) API nhanh (tham khảo)</h2>
    <pre>
// SESSION
session_start();               // bắt buộc trước khi dùng $_SESSION
$_SESSION["key"] = "value";    // set
$value = $_SESSION["key"] ?? null;
unset($_SESSION["key"]);       // xóa 1 biến session
session_destroy();             // xóa toàn bộ session (kèm cookie session)
$encoded = session_encode();   // serialize $_SESSION → chuỗi
session_decode($encoded);      // nạp chuỗi vào $_SESSION (ghi đè)
// COOKIE
setcookie(name, value, optionsArray);  // PHP 7.3+: dùng mảng options
// ví dụ:
setcookie("name", "value", [
  "expires"  => time() + 3600,
  "path"     => "/",
  "domain"   => "",
  "secure"   => true,           // chỉ qua HTTPS
  "httponly" => true,           // JS không đọc được
  "samesite" => "Lax",          // Lax/Strict/None
]);
  </pre>

    <p class="muted">
      Gợi ý luyện tập:
      (1) Thêm "ghi nhớ đăng nhập" thực sự với DB + token băm.
      (2) Tạo hệ thống flash đa loại (success/warn/danger) cho form.
      (3) Tạo middleware kiểm tra CSRF cho tất cả POST.
    </p>
  </div>
</body>

</html>
<?php
// flush output
ob_end_flush();
