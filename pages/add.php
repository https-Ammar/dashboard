<?php
require('../db.php');
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: https://hilaltiraimports.com/cars/dashboard/login.php");
    exit;
}
$brands = [];
$bresults = $conn->query("SELECT id, name FROM car_brands ORDER BY name ASC");
while ($row = $bresults->fetch_assoc()) { $brands[] = $row; }
$categories = [];
$cresults = $conn->query("
    SELECT car_categories.id, car_categories.category_name, car_brands.name AS brand_name
    FROM car_categories
    JOIN car_brands ON car_categories.brand_id = car_brands.id
    ORDER BY car_brands.name ASC, car_categories.category_name ASC
");
while ($row = $cresults->fetch_assoc()) { $categories[] = $row; }
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $price = $_POST['price'] ?? 0;
    $discount = $_POST['discount'] ?? 0;
    $monthly_payment = $_POST['monthly_payment'] ?? 0;
    $warranty_type = $_POST['warranty_type'] ?? '';
    $car_condition = $_POST['car_condition'] ?? '';
    $mileage = $_POST['mileage'] ?? 0;
    $low_mileage = $_POST['low_mileage'] ?? '';
    $brand_id = $_POST['brand_id'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $model = $_POST['model'] ?? '';
    $exterior_color = $_POST['exterior_color'] ?? '';
    $interior_color = $_POST['interior_color'] ?? '';
    $imported_from = $_POST['imported_from'] ?? '';
    $fuel_type = $_POST['fuel_type'] ?? '';
    $gear_type = $_POST['gear_type'] ?? '';
    $cylinders = $_POST['cylinders'] ?? 0;
    $engine_size = $_POST['engine_size'] ?? '';
    $drive_type = $_POST['drive_type'] ?? '';
    $keys_count = $_POST['keys_count'] ?? 0;
    $seats = $_POST['seats'] ?? 0;
    $safety_features = $_POST['safety_features'] ?? [];
    $comfort_features = $_POST['comfort_features'] ?? [];
    $tech_features = $_POST['tech_features'] ?? [];
    $exterior_features = $_POST['exterior_features'] ?? [];
    $agent_note = $_POST['agent_note'] ?? '';
    $upload_dir = '../uploads/cars/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $image_paths = [];
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $key => $name) {
            if ($_FILES['images']['error'][$key] == 0) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $new_filename = uniqid() . '.' . $ext;
                $target_file = $upload_dir . $new_filename;
                if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $target_file)) {
                    $image_paths[] = $target_file;
                }
            }
        }
    }
    if (!empty($image_paths)) {
        $images_json = json_encode($image_paths);
        $safety_json = json_encode(array_filter($safety_features));
        $comfort_json = json_encode(array_filter($comfort_features));
        $tech_json = json_encode(array_filter($tech_features));
        $exterior_json = json_encode(array_filter($exterior_features));
        $sql = "INSERT INTO car_cars (
            title, price, discount, monthly_payment, warranty_type, car_condition, mileage, low_mileage,
            brand_id, category_id, model, exterior_color, interior_color, imported_from, fuel_type,
            gear_type, cylinders, engine_size, drive_type, keys_count, seats, safety_features,
            comfort_features, tech_features, exterior_features, agent_note, images
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "siddssisiiissssssisississss",
            $title, $price, $discount, $monthly_payment, $warranty_type, $car_condition, $mileage, $low_mileage,
            $brand_id, $category_id, $model, $exterior_color, $interior_color, $imported_from, $fuel_type,
            $gear_type, $cylinders, $engine_size, $drive_type, $keys_count, $seats, $safety_json,
            $comfort_json, $tech_json, $exterior_json, $agent_note, $images_json
        );
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success text-center">تمت إضافة السيارة بنجاح!</div>';
        } else {
            $message = '<div class="alert alert-danger text-center">حدث خطأ أثناء إضافة السيارة.</div>';
        }
        $stmt->close();
    } else {
        $message = '<div class="alert alert-danger text-center">يرجى رفع صورة واحدة على الأقل.</div>';
    }
}
$cars = $conn->query("
    SELECT car_cars.*, car_brands.name AS brand_name, car_categories.category_name
    FROM car_cars
    LEFT JOIN car_brands ON car_cars.brand_id = car_brands.id
    LEFT JOIN car_categories ON car_cars.category_id = car_categories.id
    ORDER BY car_cars.id DESC
");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة سيارة</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@200;300;400;500;700;800;900&display=swap');
        * {
            font-family: 'Tajawal', sans-serif;
            font-weight: 700;
        }
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
        }
        .form-section {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .btn-custom {
            margin-bottom: 10px;
        }
        .table img {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="my-4 text-center">إضافة سيارة جديدة</h2>
        <?php echo $message; ?>
        <div class="form-section">
            <form action="add_car.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">العنوان</label>
                    <input type="text" name="title" class="form-control" required />
                </div>
                <div class="mb-3" id="images-list">
                    <label class="form-label">إضافة صورة</label>
                    <input type="file" name="images[]" class="form-control mb-2" accept="image/*" required />
                </div>
                <button class="btn btn-outline-primary btn-sm btn-custom" type="button" onclick="addImageInput()">+ صورة جديدة</button>
                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label">السعر</label>
                        <input type="number" name="price" class="form-control" required />
                    </div>
                    <div class="col">
                        <label class="form-label">الخصم</label>
                        <input type="number" name="discount" class="form-control" />
                    </div>
                    <div class="col">
                        <label class="form-label">سعر القسط شهريًا</label>
                        <input type="number" name="monthly_payment" class="form-control" />
                    </div>
                    <div class="col">
                        <label class="form-label">مفحوصة ومضمونة أو ضمان الوكالة</label>
                        <select name="warranty_type" class="form-select" required>
                            <option value="">اختر النوع</option>
                            <option value="مفحوصة ومضمونة">مفحوصة ومضمونة</option>
                            <option value="ضمان الوكالة">ضمان الوكالة</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">الحالة</label>
                    <select name="car_condition" class="form-select" required>
                        <option value="">اختر</option>
                        <option value="جديدة">جديدة</option>
                        <option value="مستعملة">مستعملة</option>
                    </select>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label">الممشى (كم)</label>
                        <input type="number" name="mileage" class="form-control" />
                    </div>
                    <div class="col">
                        <label class="form-label">هل الممشى قليل؟</label>
                        <select name="low_mileage" class="form-select">
                            <option value="">اختر</option>
                            <option value="yes">نعم</option>
                            <option value="no">لا</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label">الماركة</label>
                        <select id="brand" name="brand_id" class="form-select" required>
                            <option value="">اختر الماركة</option>
                            <?php foreach ($brands as $brand) : ?>
                                <option value="<?php echo htmlspecialchars($brand['id']); ?>">
                                    <?php echo htmlspecialchars($brand['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col">
                        <label class="form-label">الفئة</label>
                        <select id="category" name="category_id" class="form-select" required>
                            <option value="">اختَر الفئة</option>
                            <?php foreach ($categories as $cat) : ?>
                                <option value="<?php echo htmlspecialchars($cat['id']); ?>">
                                    <?php echo htmlspecialchars($cat['category_name'] . ' (' . $cat['brand_name'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label">موديل سنة</label>
                        <input type="text" name="model" class="form-control" />
                    </div>
                    <div class="col">
                        <label class="form-label">اللون الخارجي</label>
                        <input type="text" name="exterior_color" class="form-control" />
                    </div>
                    <div class="col">
                        <label class="form-label">اللون الداخلي</label>
                        <input type="text" name="interior_color" class="form-control" />
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label">الوارد</label>
                        <input type="text" name="imported_from" class="form-control" />
                    </div>
                    <div class="col">
                        <label class="form-label">نوع الوقود</label>
                        <input type="text" name="fuel_type" class="form-control" />
                    </div>
                    <div class="col">
                        <label class="form-label">نوع القير</label>
                        <input type="text" name="gear_type" class="form-control" />
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label">عدد السلندرات</label>
                        <input type="number" name="cylinders" class="form-control" />
                    </div>
                    <div class="col">
                        <label class="form-label">حجم المحرك</label>
                        <input type="text" name="engine_size" class="form-control" />
                    </div>
                    <div class="col">
                        <label class="form-label">نوع الدفع</label>
                        <input type="text" name="drive_type" class="form-control" />
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label">عدد المفاتيح</label>
                        <input type="number" name="keys_count" class="form-control" />
                    </div>
                    <div class="col">
                        <label class="form-label">عدد المقاعد</label>
                        <input type="number" name="seats" class="form-control" />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">وسائل الأمان</label>
                    <div id="safety-list" class="mb-2">
                        <input type="text" name="safety_features[]" class="form-control mb-2" placeholder="أضف وسيلة أمان" />
                    </div>
                    <button class="btn btn-outline-secondary btn-sm btn-custom" type="button" onclick="addInput('safety-list','safety_features[]','وسيلة أمان')">+ أضف وسيلة أمان</button>
                </div>
                <div class="mb-3">
                    <label class="form-label">وسائل الراحة</label>
                    <div id="comfort-list" class="mb-2">
                        <input type="text" name="comfort_features[]" class="form-control mb-2" placeholder="أضف وسيلة راحة" />
                    </div>
                    <button class="btn btn-outline-secondary btn-sm btn-custom" type="button" onclick="addInput('comfort-list','comfort_features[]','وسيلة راحة')">+ أضف وسيلة راحة</button>
                </div>
                <div class="mb-3">
                    <label class="form-label">التقنيات</label>
                    <div id="tech-list" class="mb-2">
                        <input type="text" name="tech_features[]" class="form-control mb-2" placeholder="أضف تقنية" />
                    </div>
                    <button class="btn btn-outline-secondary btn-sm btn-custom" type="button" onclick="addInput('tech-list','tech_features[]','تقنية')">+ أضف تقنية</button>
                </div>
                <div class="mb-3">
                    <label class="form-label">تجهيزات خارجية</label>
                    <div id="ext-list" class="mb-2">
                        <input type="text" name="exterior_features[]" class="form-control mb-2" placeholder="أضف تجهيز خارجي" />
                    </div>
                    <button class="btn btn-outline-secondary btn-sm btn-custom" type="button" onclick="addInput('ext-list','exterior_features[]','تجهيز خارجي')">+ أضف تجهيز خارجي</button>
                </div>
                <div class="mb-3">
                    <label class="form-label">الوكيل</label>
                    <input type="text" name="agent_note" class="form-control" />
                </div>
                <button type="submit" class="btn btn-success w-100">إضافة السيارة</button>
            </form>
        </div>
        <div class="container my-4">
            <h2 class="my-4 text-center">السيارات المضافة</h2>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>رقم السيارة</th>
                            <th>العنوان</th>
                            <th>الماركة</th>
                            <th>الفئة</th>
                            <th>السعر</th>
                            <th>الخصم</th>
                            <th>الحالة</th>
                            <th>ممشى قليل</th>
                            <th>الممشى</th>
                            <th>الضمان</th>
                            <th>الصور</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($cars && $cars->num_rows > 0) : ?>
                            <?php while ($car = $cars->fetch_assoc()) : ?>
                                <tr>
                                    <td><?php echo $car['id']; ?></td>
                                    <td><?php echo htmlspecialchars($car['title']); ?></td>
                                    <td><?php echo htmlspecialchars($car['brand_name']); ?></td>
                                    <td><?php echo htmlspecialchars($car['category_name']); ?></td>
                                    <td><?php echo number_format($car['price']); ?> ريال</td>
                                    <td><?php echo number_format($car['discount']); ?> ريال</td>
                                    <td>
                                        <span class="badge bg-<?php echo ($car['car_condition'] == 'جديدة') ? 'success' : 'secondary'; ?>">
                                            <?php echo htmlspecialchars($car['car_condition']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($car['low_mileage'] == 'yes') : ?>
                                            <span class="badge bg-info text-dark">ممشى قليل</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo number_format($car['mileage']); ?></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo htmlspecialchars($car['warranty_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $images = json_decode($car['images'] ?? '[]', true);
                                        if (!empty($images)) {
                                            foreach ($images as $image) {
                                                echo '<img src="' . htmlspecialchars($image) . '" alt="صورة السيارة" style="max-width: 100px; height: auto; margin: 5px;">';
                                            }
                                        } else {
                                            echo '<span class="text-muted">لا توجد صور</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="edit_car.php?id=<?php echo $car['id']; ?>" class="btn btn-warning btn-sm mx-1">تعديل</a>
                                        <a href="delete_car.php?id=<?php echo $car['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من الحذف؟');">حذف</a>
                                        <br><br>
                                        <a href="addtoplace.php?id=<?php echo $car['id']; ?>" class="btn btn-danger btn-sm">إضافة إلى المعرض</a>
                                        <br><br>
                                        <a href="addtotrend.php?id=<?php echo $car['id']; ?>" class="btn btn-danger btn-sm">إضافة إلى تصنيف ترند</a>
                                        <br><br>
                                        <a href="addreport.php?id=<?php echo $car['id']; ?>" class="btn btn-danger btn-sm">إضافة تقرير</a>
                                        <br><br>
                                        <a href="addcartake.php?id=<?php echo $car['id']; ?>" class="btn btn-danger btn-sm">إضافة السعر يشمل</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="12" class="text-center text-muted">لا توجد سيارات بعد.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="my-4">
            <a href="index.php" class="btn btn-primary">العودة إلى لوحة التحكم</a>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addImageInput() {
            var list = document.getElementById("images-list");
            var input = document.createElement("input");
            input.type = "file";
            input.name = "images[]";
            input.className = "form-control mb-2";
            input.accept = "image/*";
            list.appendChild(input);
        }
        function addInput(parentId, name, placeholder) {
            var list = document.getElementById(parentId);
            var input = document.createElement("input");
            input.type = "text";
            input.name = name;
            input.placeholder = placeholder;
            input.className = "form-control mb-2";
            list.appendChild(input);
        }
    </script>
</body>
</html>