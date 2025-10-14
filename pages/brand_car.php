<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة ماركات السيارات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="./main.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
        .brand-card {
            transition: all 0.3s ease;
        }
        .brand-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        button.flex.items-center.justify-center.w-full.p-3.font-medium.text-white.rounded-lg.bg-danger-500.text-theme-sm.shadow-theme-xs.hover\:bg-danger-600 {
            background: #ff0000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            width: 35px;
            height: 35px;
        }

        i.bi.bi-trash.mr-2 {
            margin: 0;
        }

        button.flex.w-full.items-center.justify-center.rounded-lg.border.border-gray-300.bg-white.p-3.text-theme-sm.font-medium.text-gray-700.shadow-theme-xs.hover\:bg-gray-50.dark\:border-gray-700.dark\:bg-gray-800.dark\:text-gray-400.dark\:hover\:bg-white\/\[0\.03\] {
            gap: 11px;
        }
    </style>
</head>
<body x-data="{ page: 'car-brands', loaded: true, darkMode: false, isModalOpen: false, editModalOpen: false, currentBrand: {} }" 
      x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode')); 
        $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)));
      " 
      :class="{'dark bg-gray-900': darkMode === true}">
    
    <?php
    require('../db.php');
    session_start();
    if (!isset($_SESSION['admin'])) {
        header("Location: login.php");
        exit;
    }

    $error = $_SESSION['error'] ?? null;
    $success = $_SESSION['success'] ?? null;
    unset($_SESSION['error']);
    unset($_SESSION['success']);

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_brand'])) {
        $brandName = trim($_POST['brandName']);
        
        if (empty($brandName)) {
            $_SESSION['error'] = "يرجى إدخال اسم الماركة.";
        } else if (!isset($_FILES["brandPhoto"]) || $_FILES["brandPhoto"]["error"] != UPLOAD_ERR_OK) {
            $_SESSION['error'] = "يرجى رفع صورة للماركة.";
        } else {
            $targetDir = "../uploads/brands/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            $fileExtension = pathinfo($_FILES["brandPhoto"]["name"], PATHINFO_EXTENSION);
            $fileName = time() . '_' . uniqid() . '.' . $fileExtension;
            $targetFilePath = $targetDir . $fileName;
            
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array(strtolower($fileExtension), $allowedTypes)) {
                $_SESSION['error'] = "نوع ملف الصورة غير مسموح به.";
            } else if (move_uploaded_file($_FILES["brandPhoto"]["tmp_name"], $targetFilePath)) {
                $photoPath = "uploads/brands/" . $fileName;
                $stmt = $conn->prepare("INSERT INTO car_brands (name, photo) VALUES (?, ?)");
                
                if ($stmt === false) {
                    $_SESSION['error'] = "خطأ في تجهيز استعلام قاعدة البيانات: " . $conn->error;
                } else {
                    $stmt->bind_param("ss", $brandName, $photoPath);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success'] = "تمت إضافة الماركة بنجاح.";
                    } else {
                        $_SESSION['error'] = "خطأ في إضافة الماركة: " . $stmt->error;
                    }
                    $stmt->close();
                }
            } else {
                $_SESSION['error'] = "خطأ في رفع الصورة.";
            }
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_brand'])) {
        $brandId = $_POST['brandId'];
        $brandName = trim($_POST['brandName']);
        
        if (empty($brandName)) {
            $_SESSION['error'] = "يرجى إدخال اسم الماركة.";
        } else {
            $stmt_select = $conn->prepare("SELECT photo FROM car_brands WHERE id = ?");
            $stmt_select->bind_param("i", $brandId);
            $stmt_select->execute();
            $result = $stmt_select->get_result();
            $oldBrandData = $result->fetch_assoc();
            $stmt_select->close();

            $updateQuery = "UPDATE car_brands SET name = ?";
            $params = [$brandName];
            $types = "s";
            
            if (isset($_FILES["brandPhoto"]) && $_FILES["brandPhoto"]["error"] == UPLOAD_ERR_OK) {
                $targetDir = "../uploads/brands/";
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                
                $fileExtension = pathinfo($_FILES["brandPhoto"]["name"], PATHINFO_EXTENSION);
                $fileName = time() . '_' . uniqid() . '.' . $fileExtension;
                $targetFilePath = $targetDir . $fileName;
                
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array(strtolower($fileExtension), $allowedTypes)) {
                    $_SESSION['error'] = "نوع ملف الصورة غير مسموح به.";
                } else if (move_uploaded_file($_FILES["brandPhoto"]["tmp_name"], $targetFilePath)) {
                    $photoPath = "uploads/brands/" . $fileName;
                    $updateQuery .= ", photo = ?";
                    $params[] = $photoPath;
                    $types .= "s";
                    
                    if ($oldBrandData && !empty($oldBrandData['photo'])) {
                        $oldPhotoPath = "../" . $oldBrandData['photo'];
                        if (file_exists($oldPhotoPath)) {
                            unlink($oldPhotoPath);
                        }
                    }
                } else {
                    $_SESSION['error'] = "خطأ في رفع الصورة.";
                }
            }
            
            if (!isset($_SESSION['error'])) {
                $updateQuery .= " WHERE id = ?";
                $params[] = $brandId;
                $types .= "i";
                
                $stmt = $conn->prepare($updateQuery);
                if ($stmt === false) {
                    $_SESSION['error'] = "خطأ في تجهيز استعلام قاعدة البيانات: " . $conn->error;
                } else {
                    $stmt->bind_param($types, ...$params);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success'] = "تم تحديث الماركة بنجاح.";
                    } else {
                        $_SESSION['error'] = "خطأ في تحديث الماركة: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_brand'])) {
        $brandId = $_POST['brandIdToDelete'];
        
        $stmt_select = $conn->prepare("SELECT photo FROM car_brands WHERE id = ?");
        $stmt_select->bind_param("i", $brandId);
        $stmt_select->execute();
        $result = $stmt_select->get_result();
        $brand_data = $result->fetch_assoc();
        $stmt_select->close();

        if ($brand_data) {
            $stmt_delete = $conn->prepare("DELETE FROM car_brands WHERE id = ?");
            $stmt_delete->bind_param("i", $brandId);

            if ($stmt_delete->execute()) {
                $photoPath = "../" . $brand_data['photo'];
                if (!empty($brand_data['photo']) && file_exists($photoPath)) {
                    unlink($photoPath);
                }
                $_SESSION['success'] = "تم حذف الماركة بنجاح.";
            } else {
                $_SESSION['error'] = "خطأ في حذف الماركة: " . $stmt_delete->error;
            }
            $stmt_delete->close();
        } else {
            $_SESSION['error'] = "لم يتم العثور على الماركة للحذف.";
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $whereClause = "";
    if (!empty($search)) {
        $whereClause = " WHERE name LIKE ?";
    }
    
    $query = "SELECT id, name, photo FROM car_brands" . $whereClause . " ORDER BY id DESC";
    $stmt_brands = $conn->prepare($query);

    if (!empty($search)) {
        $searchTerm = '%' . $search . '%';
        $stmt_brands->bind_param("s", $searchTerm);
    }

    $stmt_brands->execute();
    $brands = $stmt_brands->get_result();
    ?>
    
    <div x-show="loaded" x-transition.opacity x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 500)})" class="fixed inset-0 z-999999 flex items-center justify-center bg-white dark:bg-black">
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
    </div>
    
    <?php include '../includes/nav.php'; ?>
    
    <div class="flex h-screen overflow-hidden">
        <?php include '../includes/header.php'; ?>
        <div class="relative flex flex-1 flex-col overflow-x-hidden overflow-y-auto">
            
            <div x-show="isModalOpen" x-transition="" class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto z-99999">
                <div class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]" @click="isModalOpen=false"></div>
                <div @click.outside="isModalOpen=false" class="no-scrollbar relative w-full max-w-[500px] overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8">
                    <div class="px-2">
                        <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">إضافة ماركة سيارة جديدة</h4>
                        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">إدارة ماركات السيارات</p>
                    </div>
                    <form class="flex flex-col" method="POST" enctype="multipart/form-data">
                        <div class="custom-scrollbar overflow-y-auto px-2">
                            <div class="grid grid-cols-1 gap-y-5">
                                <div>
                                    <label for="brandName" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">اسم الماركة</label>
                                    <input type="text" name="brandName" id="brandName" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                                <div>
                                    <label for="brandPhoto" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">صورة الماركة</label>
                                    <input type="file" name="brandPhoto" id="brandPhoto" accept="image/*" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-center gap-6 px-2 mt-6 sm:flex-row sm:justify-between">
                            <div class="flex items-center w-full gap-3 sm:w-auto">
                                <button @click="isModalOpen=false" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto">إلغاء</button>
                                <button type="submit" name="add_brand" class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">إضافة الماركة</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div x-show="editModalOpen" x-transition="" class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto z-99999">
                <div class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]" @click="editModalOpen=false"></div>
                <div @click.outside="editModalOpen=false" class="no-scrollbar relative w-full max-w-[500px] overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8">
                    <div class="px-2">
                        <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">تعديل ماركة سيارة</h4>
                        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7" x-text="'تعديل بيانات الماركة: ' + currentBrand.name"></p>
                    </div>
                    <form class="flex flex-col" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="brandId" x-model="currentBrand.id">
                        <div class="custom-scrollbar overflow-y-auto px-2">
                            <div class="grid grid-cols-1 gap-y-5">
                                <div>
                                    <label for="editBrandName" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">اسم الماركة</label>
                                    <input type="text" name="brandName" id="editBrandName" x-model="currentBrand.name" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                                <div>
                                    <label for="editBrandPhoto" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">صورة الماركة</label>
                                    <input type="file" name="brandPhoto" id="editBrandPhoto" accept="image/*" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <p class="mt-2 text-xs text-gray-500">اترك الحقل فارغًا إذا كنت لا تريد تغيير الصورة</p>
                                </div>
                                <div x-show="currentBrand.photo">
                                    <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">الصورة الحالية</p>
                                    <img :src="'../' + currentBrand.photo" :alt="currentBrand.name" class="h-20 w-20 rounded-lg object-cover">
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-center gap-6 px-2 mt-6 sm:flex-row sm:justify-between">
                            <div class="flex items-center w-full gap-3 sm:w-auto">
                                <button @click="editModalOpen=false" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto">إلغاء</button>
                                <button type="submit" name="update_brand" class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">تحديث الماركة</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <main>
                <div class="mx-auto p-4 md:p-6">
                    <div class="col-span-12">
                        <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                            
                            <?php if ($success): ?>
                                <div class="mb-4 bg-success-500/10 border border-success-500 text-success-700 p-4 rounded-lg dark:bg-success-900/20 dark:border-success-700 dark:text-success-400" role="alert">
                                    <?php echo htmlspecialchars($success); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($error): ?>
                                <div class="mb-4 bg-danger-500/10 border border-danger-500 text-danger-700 p-4 rounded-lg dark:bg-danger-900/20 dark:border-danger-700 dark:text-danger-400" role="alert">
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>

                            <div class="mb-4 flex flex-col gap-2 px-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">الماركات المضافة</h3>
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                    <form method="GET" action="" class="flex">
                                        <div class="relative">
                                            <span class="absolute -translate-y-1/2 pointer-events-none top-1/2 left-4">
                                                <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" fill=""></path>
                                                </svg>
                                            </span>
                                            <input type="text" name="search" placeholder="بحث..." value="<?php echo htmlspecialchars($search); ?>" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                            <button type="submit" class="hidden"></button>
                                        </div>
                                    </form>
                                    <div>
                                        <button @click="isModalOpen=true" class="text-theme-sm shadow-theme-xs inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                                            <svg class="stroke-current fill-white dark:fill-gray-800" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M2.29004 5.90393H17.7067" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M17.7075 14.0961H2.29085" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M12.0826 3.33331C13.5024 3.33331 14.6534 4.48431 14.6534 5.90414C14.6534 7.32398 13.5024 8.47498 12.0826 8.47498C10.6627 8.47498 9.51172 7.32398 9.51172 5.90415C9.51172 4.48432 10.6627 3.33331 12.0826 3.33331Z" fill="" stroke="" stroke-width="1.5"></path>
                                                <path d="M7.91745 11.525C6.49762 11.525 5.34662 12.676 5.34662 14.0959C5.34661 15.5157 6.49762 16.6667 7.91745 16.6667C9.33728 16.6667 10.4883 15.5157 10.4883 14.0959C10.4883 12.676 9.33728 11.525 7.91745 11.525Z" fill="" stroke="" stroke-width="1.5"></path>
                                            </svg>
                                            إضافة ماركة
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="p-5">
                                <?php if ($brands && $brands->num_rows > 0): ?>
                                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                        <?php while ($brand = $brands->fetch_assoc()): ?>
                                            <div class="brand-card rounded-2xl bg-gray-100 p-5 dark:bg-white/[0.03]">
                                                <div class="flex items-center justify-between pb-5 mb-5 border-b border-gray-200 dark:border-gray-800">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10">
                                                            <?php if ($brand['photo']): ?>
                                                                <img src="../<?php echo htmlspecialchars($brand['photo']); ?>" alt="<?php echo htmlspecialchars($brand['name']); ?>" class="rounded-full object-cover w-10 h-10">
                                                            <?php else: ?>
                                                                <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                                    <span class="text-gray-600 text-xs">لا توجد صورة</span>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div>
                                                            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90"><?php echo htmlspecialchars($brand['name']); ?></h3>
                                                            <span class="block text-gray-500 text-theme-xs dark:text-gray-400">ماركة سيارة</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h4 class="mb-1 font-medium text-right text-gray-700 text-theme-sm dark:text-gray-400">ID: <?php echo $brand['id']; ?></h4>
                                                        <span class="flex items-center justify-end gap-1 font-medium text-theme-xs text-success-600 dark:text-success-500">
                                                            <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.56462 1.62394C5.70193 1.47073 5.90135 1.37433 6.12329 1.37433C6.1236 1.37433 6.12391 1.37433 6.12422 1.37433C6.31631 1.37416 6.50845 1.44732 6.65505 1.59381L9.65514 4.59181C9.94814 4.8846 9.94831 5.35947 9.65552 5.65247C9.36273 5.94546 8.88785 5.94563 8.59486 5.65284L6.87329 3.93248L6.87329 10.125C6.87329 10.5392 6.53751 10.875 6.12329 10.875C5.70908 10.875 5.37329 10.5392 5.37329 10.125L5.37329 3.93579L3.65516 5.65282C3.36218 5.94562 2.8873 5.94547 2.5945 5.65249C2.3017 5.3595 2.30185 4.88463 2.59484 4.59183L5.56462 1.62394Z" fill=""></path>
                                                            </svg>
                                                            نشط
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <button @click="currentBrand = <?php echo htmlspecialchars(json_encode($brand)); ?>; editModalOpen = true;" type="button" class="flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white p-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                                                        <i class="bi bi-pencil-square mr-2"></i>
                                                        تعديل
                                                    </button>
                                                    <form method="POST" action="" style="display:inline;">
                                                        <input type="hidden" name="brandIdToDelete" value="<?php echo $brand['id']; ?>">
                                                        <button type="submit" name="delete_brand" class="flex items-center justify-center w-full p-3 font-medium text-white rounded-lg bg-danger-500 text-theme-sm shadow-theme-xs hover:bg-danger-600" onclick="return confirm('هل أنت متأكد من حذف الماركة: <?php echo htmlspecialchars($brand['name']); ?>؟')">
                                                            <i class="bi bi-trash mr-2"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-10">
                                        <p class="text-gray-500 dark:text-gray-400">
                                            <?php echo !empty($search) ? 'لا توجد نتائج بحث لـ: ' . htmlspecialchars($search) : 'لا توجد ماركات سيارات مضافة حاليًا.'; ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>