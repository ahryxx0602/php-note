<?php

header('Content-Type: text/html; charset=UTF-8');

// Helper in-page UI
function h1($t)
{
              echo "<h1 style='font-family:system-ui;margin:12px 0;'>$t</h1>";
}
function h2($t)
{
              echo "<h2 style='font-family:system-ui;margin:8px 0;color:#444;'>$t</h2>";
}
function code($t)
{
              echo "<pre style='background:#0f172a;color:#e2e8f0;padding:12px;border-radius:10px;overflow:auto;'>$t</pre>";
}
function sep()
{
              echo "<hr style='margin:18px 0;border:0;border-top:1px solid #ddd;'/>";
}

echo "<div style='max-width:960px;margin:24px auto;padding:0 12px;font-family:system-ui;line-height:1.6'>";

h1("PHP OOP – All in One");
echo "<p>Trang này trình diễn: <b>Class, Object, Property, Method, Constructor, Destructor, Static, Const, Final, Trait</b> và 4 tính chất OOP: <b>Đóng gói, Kế thừa, Đa hình, Trừu tượng</b>.</p>";

/* =====================================================
   1) CLASS, OBJECT, PROPERTY, METHOD
   ===================================================== */
h2("1) Class, Object, Property, Method");
class CarBasic
{
              // Property
              public string $brand;
              public string $color;

              // Method
              public function setInfo(string $brand, string $color): void
              {
                            $this->brand = $brand;
                            $this->color = $color;
              }
              public function info(): string
              {
                            return "Xe: {$this->brand}, màu: {$this->color}";
              }
}
$car1 = new CarBasic(); // Object
$car1->setInfo("Toyota", "Đỏ");
echo "<p>" . $car1->info() . "</p>";

/* =====================================================
   2) CONSTRUCTOR / DESTRUCTOR
   ===================================================== */
sep();
h2("2) Constructor / Destructor");
class UserCD
{
              public function __construct(private string $name)
              {
                            echo "<p>HELLO {$this->name}</p>";
              }
              public function run(): void
              {
                            echo "<p>{$this->name} đang chạy...</p>";
              }
              public function __destruct()
              {
                            // Lưu ý: phần này in khi script kết thúc hoặc object bị hủy.
                            echo "<p><i>Destructor:</i> Đối tượng UserCD kết thúc.</p>";
              }
}
$u = new UserCD("Thành");
$u->run();

/* =====================================================
   3) STATIC / CONST / FINAL
   ===================================================== */
sep();
h2("3) Static / Const / Final");
class StudentCounter
{
              public static int $count = 0;     // Static property dùng chung
              public const SCHOOL = "DTU";      // Const: hằng trong class
              public function __construct()
              {
                            self::$count++;
              }
              public static function check(): string
              {
                            return "Số lượng: " . self::$count;
              }
}
echo "<p>Trường: " . StudentCounter::SCHOOL . "</p>";
echo "<p>" . StudentCounter::check() . "</p>";
new StudentCounter();
new StudentCounter();
echo "<p>" . StudentCounter::check() . "</p>";

class BaseLock
{
              public final function lock(): string
              { // final: không thể override ở class con
                            return "Đã khóa logic quan trọng.";
              }
}
class ChildLock extends BaseLock
{
              // Thử override sẽ lỗi: 
              // public function lock(){}  // ❌ Không được vì final
}
$child = new ChildLock();
echo "<p>" . $child->lock() . "</p>";

/* =====================================================
   4) TRAIT – Trộn hành vi dùng chung
   ===================================================== */
sep();
h2("4) Trait");
trait Loggable
{
              public function log(string $msg): void
              {
                            echo "<p>[LOG] $msg</p>";
              }
}
trait Flyable
{
              public function fly(): string
              {
                            return "Đang bay...";
              }
}
class Drone
{
              use Loggable, Flyable;
              public function start(): string
              {
                            return "Drone khởi động";
              }
}
$dr = new Drone();
$dr->log("Chuẩn bị bay");
echo "<p>" . $dr->start() . "</p>";
echo "<p>" . $dr->fly() . "</p>";

/* =====================================================
   5) ENCAPSULATION (Đóng gói) + Getter/Setter
   ===================================================== */
sep();
h2("5) Đóng gói (Encapsulation)");
class Bank
{
              // Ẩn dữ liệu:
              private int $balance = 100;

              public function getBalance(): int
              {
                            return $this->balance;
              }
              public function deposit(int $amount): void
              {
                            if ($amount > 0) $this->balance += $amount;
              }
              public function withdraw(int $amount): bool
              {
                            if ($amount > 0 && $amount <= $this->balance) {
                                          $this->balance -= $amount;
                                          return true;
                            }
                            return false;
              }
}
$bank = new Bank();
$bank->deposit(1000);
$ok = $bank->withdraw(500);
echo "<p>Rút 500: " . ($ok ? "Thành công" : "Thất bại") . "</p>";
echo "<p>Số dư hiện tại: " . $bank->getBalance() . "</p>";

/* =====================================================
   6) INHERITANCE (Kế thừa) + OVERRIDE
   ===================================================== */
sep();
h2("6) Kế thừa (Inheritance) + Override");
class Vehicle
{
              public function horn(): string
              {
                            return "Tít tít";
              }
}
class Car extends Vehicle
{
              // Override method từ cha
              public function horn(): string
              {
                            return "Bíp bíp (đã ghi đè)";
              }
}
$v = new Vehicle();
$c = new Car();
echo "<p>Vehicle: " . $v->horn() . "</p>";
echo "<p>Car: " . $c->horn() . "</p>";

/* =====================================================
   7) POLYMORPHISM (Đa hình)
   ===================================================== */
sep();
h2("7) Đa hình (Polymorphism)");
class Animal
{
              public function speak(): string
              {
                            return "...";
              }
}
class Dog extends Animal
{
              public function speak(): string
              {
                            return "Gâu gâu";
              }
}
class Cat extends Animal
{
              public function speak(): string
              {
                            return "Meo meo";
              }
}
$animals = [new Dog(), new Cat()];
foreach ($animals as $a) {
              echo "<p>" . $a->speak() . "</p>";
}

/* =====================================================
   8) ABSTRACTION (Trừu tượng) – abstract class & interface
   ===================================================== */
sep();
h2("8) Trừu tượng (Abstraction)");

// 8.1 Abstract class
abstract class Employee
{
              public function checkIn(): string
              {
                            return "Đã điểm danh.";
              }
              abstract public function work(): string;  // bắt buộc class con cài đặt
}
class Engineer extends Employee
{
              public function work(): string
              {
                            return "Thi công công trình";
              }
}
class Manager extends Employee
{
              public function work(): string
              {
                            return "Quản lý nhân sự & tiến độ";
              }
}
$e = new Engineer();
$m = new Manager();
echo "<p>Kỹ sư: " . $e->checkIn() . " - " . $e->work() . "</p>";
echo "<p>Tổ trưởng/Quản lý: " . $m->checkIn() . " - " . $m->work() . "</p>";

// 8.2 Interface
interface Payment
{
              public function transfer(int $amount): string;
              public function sender(string $name): string;
}
class MoMo implements Payment
{
              public function transfer(int $amount): string
              {
                            return "Chuyển $amount qua MoMo";
              }
              public function sender(string $name): string
              {
                            return "Người chuyển: $name";
              }
}
class ZaloPay implements Payment
{
              public function transfer(int $amount): string
              {
                            return "Chuyển $amount qua ZaloPay";
              }
              public function sender(string $name): string
              {
                            return "Người chuyển: $name";
              }
}
$p1 = new MoMo();
$p2 = new ZaloPay();
echo "<p>" . $p1->transfer(150) . " | " . $p1->sender("Thành") . "</p>";
echo "<p>" . $p2->transfer(200) . " | " . $p2->sender("Đạt") . "</p>";

/* =====================================================
   9) SCOPE (Public / Protected / Private)
   ===================================================== */
sep();
h2("9) Phạm vi (Public / Protected / Private)");
class UserScope
{
              public string $name = "Thanh";   // truy cập mọi nơi
              protected string $role = "user"; // chỉ trong class & class con
              private string $secret = "abc";  // chỉ trong chính class này

              public function show(): string
              {
                            return "$this->name / $this->role / $this->secret";
              }
}
class AdminScope extends UserScope
{
              public function showRole(): string
              {
                            return $this->role;
              } // OK (protected)
              // $this->secret; // ❌ không truy cập được (private)
}
$us = new UserScope();
$ad = new AdminScope();
echo "<p>" . $us->show() . "</p>";
echo "<p>Role từ class con: " . $ad->showRole() . "</p>";

/* =====================================================
   10) TỔNG KẾT
   ===================================================== */
sep();
h2("Tổng kết nhanh");
echo "<ul>
  <li><b>Class/Object</b>: khuôn & thể hiện dữ liệu/hành vi.</li>
  <li><b>Property/Method</b>: trạng thái & hành động của đối tượng.</li>
  <li><b>Constructor/Destructor</b>: khởi tạo & dọn dẹp.</li>
  <li><b>Static/Const/Final</b>: tài nguyên chung, hằng, chặn override/kế thừa.</li>
  <li><b>Trait</b>: trộn hành vi dùng chung (giải pháp cho đa kế thừa).</li>
  <li><b>Encapsulation</b>: ẩn dữ liệu, expose qua getter/setter an toàn.</li>
  <li><b>Inheritance</b>: tái sử dụng & mở rộng hành vi (override).</li>
  <li><b>Polymorphism</b>: cùng interface/hàm – nhiều cách cài đặt.</li>
  <li><b>Abstraction</b>: định nghĩa hợp đồng (abstract/interface), tách cái <i>làm gì</i> khỏi <i>làm thế nào</i>.</li>
</ul>";

echo "<p style='margin-top:24px;color:#555'>Gợi ý luyện tập: 
<code>PaymentFactory</code>, 
mở rộng <code>Trait Loggable</code> để ghi log ra file, 
và thêm <code>Repository</code> dùng <i>interface</i> với 2 cài đặt: InMemory / MySQL.</p>";

echo "</div>";
