<?php
require('../db.php');

session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: https://hilaltiraimports.com/cars/dashboard/login.php");
    exit;
}

$search = '';
$where_conditions = [];
$params = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    if (!empty($search)) {
        $where_conditions[] = "(a.name LIKE '%$search%' OR a.city LIKE '%$search%' OR a.phone LIKE '%$search%')";
    }
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_service_center'])) {
        $agent_name = $conn->real_escape_string($_POST['agent_name']);
        $city = $conn->real_escape_string($_POST['city']);
        $phone_number = $conn->real_escape_string($_POST['phone']);
        $iframe = $conn->real_escape_string($_POST['iframe_code']);
        
        $sql = "INSERT INTO agents (name, city, phone, iframe_code) VALUES ('$agent_name', '$city', '$phone_number', '$iframe')";
        
        if ($conn->query($sql) === TRUE) {
            $message = "تم إضافة مركز الصيانة بنجاح!";
            $message_type = "success";
        } else {
            $message = "حدث خطأ أثناء إضافة مركز الصيانة: " . $conn->error;
            $message_type = "danger";
        }
    }
    
    if (isset($_POST['update_service_center'])) {
        $agent_id = $conn->real_escape_string($_POST['agent_id']);
        $agent_name = $conn->real_escape_string($_POST['agent_name']);
        $city = $conn->real_escape_string($_POST['city']);
        $phone_number = $conn->real_escape_string($_POST['phone']);
        $iframe = $conn->real_escape_string($_POST['iframe_code']);
        
        $sql = "UPDATE agents SET name='$agent_name', city='$city', phone='$phone_number', iframe_code='$iframe' WHERE id='$agent_id'";
        
        if ($conn->query($sql) === TRUE) {
            $message = "تم تحديث مركز الصيانة بنجاح!";
            $message_type = "success";
        } else {
            $message = "حدث خطأ أثناء تحديث مركز الصيانة: " . $conn->error;
            $message_type = "danger";
        }
    }
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM agents WHERE id = $delete_id";
    
    if ($conn->query($sql) === TRUE) {
        $message = "تم حذف مركز الصيانة بنجاح!";
        $message_type = "success";
    } else {
        $message = "حدث خطأ أثناء حذف مركز الصيانة: " . $conn->error;
        $message_type = "danger";
    }
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 8;
$offset = ($page - 1) * $per_page;

$agents = [];
$result_count = $conn->query("
    SELECT COUNT(*) as total 
    FROM agents a 
    $where_clause
");
$total_agents = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_agents / $per_page);

$resultAgents = $conn->query("
    SELECT a.*, GROUP_CONCAT(cb.name SEPARATOR ', ') as brand_names
    FROM agents a
    LEFT JOIN agent_brands ab ON a.id = ab.agent_id
    LEFT JOIN car_brands cb ON ab.brand_id = cb.id
    $where_clause
    GROUP BY a.id
    ORDER BY a.id DESC
    LIMIT $offset, $per_page
");
while ($row = $resultAgents->fetch_assoc()) {
    $agents[] = $row;
}

$jeddah_count = 0;
$makkah_count = 0;
$riyadh_count = 0;

$result_cities = $conn->query("
    SELECT city, COUNT(*) as count 
    FROM agents 
    GROUP BY city
");
while ($row = $result_cities->fetch_assoc()) {
    if ($row['city'] == 'جدة') $jeddah_count = $row['count'];
    if ($row['city'] == 'مكة المكرمة') $makkah_count = $row['count'];
    if ($row['city'] == 'الرياض') $riyadh_count = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة مركز صيانة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="./main.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
    </style>
</head>
<body x-data="{ page: 'create-service-center', loaded: true, darkMode: false, stickyMenu: false, sidebarToggle: false, scrollTop: false, isTaskModalModal: false, isEditModalModal: false, editData: {} }" x-init="darkMode = JSON.parse(localStorage.getItem('darkMode')); $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))" :class="{'dark bg-gray-900': darkMode === true}">
    <div x-show="loaded" x-transition.opacity x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 500)})" class="fixed inset-0 z-999999 flex items-center justify-center bg-white dark:bg-black">
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
    </div>

    <?php include '../includes/nav.php'; ?>
    
    <div class="flex h-screen overflow-hidden">
        <?php include '../includes/header.php'; ?>

        <div class="relative flex flex-1 flex-col overflow-x-hidden overflow-y-auto">

            <?php if (isset($message)): ?>
            <div class="m-4 rounded-lg p-4 <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <div x-show="isTaskModalModal" x-transition="" class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto z-99999">
                <div class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]" @click="isTaskModalModal=false"></div>

                
                <div @click.outside="isTaskModalModal=false" class="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-11">
                    <div class="px-2">
                        <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">إضافة مركز صيانة جديد</h4>
                        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">إدارة مراكز الصيانة بسهولة</p>
                    </div>

                    
                    <form class="flex flex-col" method="POST">
                        <div class="custom-scrollbar overflow-y-auto px-2">
                            <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">اسم الوكيل</label>
                                    <input type="text" name="agent_name" value="" placeholder="أدخل اسم الوكيل" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">اسم المدينة</label>
                                    <select name="city" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                        <option value="">اختر المدينة</option>
                                        <option value="جدة">جدة</option>
                                        <option value="مكة المكرمة">مكة المكرمة</option>
                                        <option value="المدينة المنورة">المدينة المنورة</option>
                                        <option value="الرياض">الرياض</option>
                                        <option value="الدمام">الدمام</option>
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">رقم الهاتف</label>
                                    <input type="text" name="phone" value="" placeholder="أدخل رقم الهاتف" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">كود iframe أو الرابط</label>
                                    <textarea name="iframe_code" placeholder="أدخل كود التضمين أو الرابط" required class="dark:bg-dark-900 h-24 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-center gap-6 px-2 mt-6 sm:flex-row sm:justify-between">
                            <div class="flex items-center w-full gap-3 sm:w-auto">
                                <button @click="isTaskModalModal=false" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto">إلغاء</button>
                                <button type="submit" name="add_service_center" class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">إضافة المركز</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div x-show="isEditModalModal" x-transition="" class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto z-99999">
                <div class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]" @click="isEditModalModal=false"></div>
                <div @click.outside="isEditModalModal=false" class="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-11">
                    <div class="px-2">
                        <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">تعديل مركز الصيانة</h4>
                        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">تعديل بيانات مركز الصيانة</p>
                    </div>
                    <form class="flex flex-col" method="POST">
                        <input type="hidden" name="agent_id" x-model="editData.id">
                        <div class="custom-scrollbar overflow-y-auto px-2">
                            <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">اسم الوكيل</label>
                                    <input x-model="editData.agent_name" type="text" name="agent_name" placeholder="أدخل اسم الوكيل" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">اسم المدينة</label>
                                    <select x-model="editData.city" name="city" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                        <option value="">اختر المدينة</option>
                                        <option value="جدة">جدة</option>
                                        <option value="مكة المكرمة">مكة المكرمة</option>
                                        <option value="المدينة المنورة">المدينة المنورة</option>
                                        <option value="الرياض">الرياض</option>
                                        <option value="الدمام">الدمام</option>
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">رقم الهاتف</label>
                                    <input x-model="editData.phone" type="text" name="phone" placeholder="أدخل رقم الهاتف" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">كود iframe أو الرابط</label>
                                    <textarea x-model="editData.iframe_code" name="iframe_code" placeholder="أدخل كود التضمين أو الرابط" required class="dark:bg-dark-900 h-24 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-center gap-6 px-2 mt-6 sm:flex-row sm:justify-between">
                            <div class="flex items-center w-full gap-3 sm:w-auto">
                                <button @click="isEditModalModal=false" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto">إلغاء</button>
                                <button type="submit" name="update_service_center" class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">تحديث المركز</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <main>
                <div class="mx-auto p-4 md:p-6">
                    <div class=" mb-8  rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="grid rounded-2xl border border-gray-200 bg-white sm:grid-cols-2 xl:grid-cols-4 dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-200 px-6 py-5 sm:border-r xl:border-b-0 dark:border-gray-800">
                                <span class="text-sm text-gray-500 dark:text-gray-400">عدد مراكز الصيانة</span>
                                <div class="mt-2 flex items-end gap-3">
                                    <h4 class="text-title-xs sm:text-title-sm font-bold text-gray-800 dark:text-white/90">
                                        <?php echo $total_agents; ?>                                    </h4>
                                    <div>
                                        <span class="bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500 flex items-center gap-1 rounded-full py-0.5 pr-2.5 pl-2 text-sm font-medium">+</span>
                                    </div>
                                </div>
                            </div>
                            <div class="border-b border-gray-200 px-6 py-5 xl:border-r xl:border-b-0 dark:border-gray-800">
                                <span class="text-sm text-gray-500 dark:text-gray-400">مراكز جدة</span>
                                <div class="mt-2 flex items-end gap-3">
                                    <h4 class="text-title-xs sm:text-title-sm font-bold text-gray-800 dark:text-white/90">
                                        <?php echo $jeddah_count; ?>                                    </h4>
                                    <div>
                                        <span class="bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500 flex items-center gap-1 rounded-full py-0.5 pr-2.5 pl-2 text-sm font-medium">+</span>
                                    </div>
                                </div>
                            </div>
                            <div class="border-b border-gray-200 px-6 py-5 sm:border-r sm:border-b-0 dark:border-gray-800">
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">مراكز مكة</span>
                                    <div class="mt-2 flex items-end gap-3">
                                        <h4 class="text-title-xs sm:text-title-sm font-bold text-gray-800 dark:text-white/90">
                                            <?php echo $makkah_count; ?>                                        </h4>
                                        <div>
                                            <span class="bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500 flex items-center gap-1 rounded-full py-0.5 pr-2.5 pl-2 text-sm font-medium">+</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="border-b border-gray-200 px-6 py-5 sm:border-r sm:border-b-0 dark:border-gray-800">
                                <span class="text-sm text-gray-500 dark:text-gray-400">مراكز الرياض</span>
                                <div class="mt-2 flex items-end gap-3">
                                    <h4 class="text-title-xs sm:text-title-sm font-bold text-gray-800 dark:text-white/90">
                                        <?php echo $riyadh_count; ?>                                    </h4>
                                    <div>
                                        <span class="bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500 flex items-center gap-1 rounded-full py-0.5 pr-2.5 pl-2 text-sm font-medium">+</span>
                                    </div>
                                </div>
                            </div>
                        </div>
            
                    </div>
                    <div class="col-span-12">
                        <div class="rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
                            <div class="mb-4 flex flex-col gap-2 px-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">مراكز الصيانة</h3>
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                    <form method="GET" action="">
                                        <div class="relative">
                                            <span class="absolute -translate-y-1/2 pointer-events-none top-1/2 left-4">
                                                <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" fill=""></path>
                                                </svg>
                                            </span>
                                            <input type="text" name="search" placeholder="بحث..." value="<?php echo htmlspecialchars($search); ?>" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                        </div>
                                    </form>
                                    <div>
                                        <button @click="isTaskModalModal=true" class="text-theme-sm shadow-theme-xs inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                                            <svg class="stroke-current fill-white dark:fill-gray-800" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M2.29004 5.90393H17.7067" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M17.7075 14.0961H2.29085" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M12.0826 3.33331C13.5024 3.33331 14.6534 4.48431 14.6534 5.90414C14.6534 7.32398 13.5024 8.47498 12.0826 8.47498C10.6627 8.47498 9.51172 7.32398 9.51172 5.90415C9.51172 4.48432 10.6627 3.33331 12.0826 3.33331Z" fill="" stroke="" stroke-width="1.5"></path>
                                                <path d="M7.91745 11.525C6.49762 11.525 5.34662 12.676 5.34662 14.0959C5.34661 15.5157 6.49762 16.6667 7.91745 16.6667C9.33728 16.6667 10.4883 15.5157 10.4883 14.0959C10.4883 12.676 9.33728 11.525 7.91745 11.525Z" fill="" stroke="" stroke-width="1.5"></path>
                                            </svg>
                                            إضافة مركز
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="custom-scrollbar max-w-full overflow-x-auto overflow-y-visible px-5 sm:px-6">
                                <table class="min-w-full">
                                    <thead class="border-y border-gray-100 py-3 dark:border-gray-800">
                                        <tr>
                                            <th class="py-3 pr-5 font-normal whitespace-nowrap sm:pr-6">
                                                <div class="flex items-center">
                                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">#</p>
                                                </div>
                                            </th>
                                            <th class="py-3 pr-5 font-normal whitespace-nowrap sm:pr-6">
                                                <div class="flex items-center">
                                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">اسم مركز الصيانة</p>
                                                </div>
                                            </th>
                                            <th class="px-5 py-3 font-normal whitespace-nowrap sm:px-6">
                                                <div class="flex items-center">
                                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">المدينة</p>
                                                </div>
                                            </th>
                                            <th class="px-5 py-3 font-normal whitespace-nowrap sm:px-6">
                                                <div class="flex items-center">
                                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">رقم الهاتف</p>
                                                </div>
                                            </th>
                                            <th class="px-5 py-3 font-normal whitespace-nowrap sm:px-6">
                                                <div class="flex items-center">
                                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">الموقع iframe</p>
                                                </div>
                                            </th>
                                            <th class="px-5 py-3 font-normal whitespace-nowrap sm:px-6">
                                                <div class="flex items-center">
                                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">إجراء</p>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <?php if (count($agents) > 0): ?>
                                            <?php $rownum = ($page - 1) * $per_page + 1; ?>
                                            <?php foreach ($agents as $agent): ?>
                                                <tr>
                                                    <td class="py-3 pr-5 whitespace-nowrap sm:pr-5">
                                                        <div class="flex items-center">
                                                            <p class="text-theme-sm text-gray-700 dark:text-gray-400"><?php echo $rownum++; ?></p>
                                                        </div>
                                                    </td>
                                                    <td class="py-3 pr-5 whitespace-nowrap sm:pr-5">
                                                        <div class="flex items-center">
                                                            <p class="text-theme-sm text-gray-700 dark:text-gray-400"><?php echo htmlspecialchars($agent['name']); ?></p>
                                                        </div>
                                                    </td>
                                                    <td class="px-5 py-3 whitespace-nowrap sm:px-6">
                                                        <div class="flex items-center">
                                                            <p class="text-theme-sm text-gray-700 dark:text-gray-400"><?php echo htmlspecialchars($agent['city']); ?></p>
                                                        </div>
                                                    </td>
                                                    <td class="px-5 py-3 whitespace-nowrap sm:px-6">
                                                        <div class="flex items-center">
                                                            <p class="text-theme-sm text-gray-700 dark:text-gray-400"><?php echo htmlspecialchars($agent['phone']); ?></p>
                                                        </div>
                                                    </td>
                                                    <td class="px-5 py-3 whitespace-nowrap sm:px-6">
                                                        <div class="flex items-center">
                                                            <p class="text-theme-sm text-gray-700 dark:text-gray-400 truncate max-w-xs"><?php echo !empty($agent['iframe_code']) ? htmlspecialchars($agent['iframe_code']) : 'لا يوجد'; ?></p>
                                                        </div>
                                                    </td>
                                                    <td class="px-5 py-3 whitespace-nowrap sm:px-6">
                                                        <div class="flex items-center justify-center">
                                                            <div x-data="dropdown()" class="relative">
                                                                <button @click="toggle" class="text-gray-500 dark:text-gray-400">
                                                                    <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.749 11.0285 19.749 11.995V12.005C19.749 12.9715 18.9655 13.755 17.999 13.755C17.0325 13.755 16.249 12.9715 16.249 12.005V11.995C16.249 11.0285 17.0325 10.245 17.999 10.245ZM13.749 11.995C13.749 11.0285 12.9655 10.245 11.999 10.245C11.0325 10.245 10.249 11.0285 10.249 11.995V12.005C10.249 12.9715 11.0325 13.755 11.999 13.755C12.9655 13.755 13.749 12.9715 13.749 12.005V11.995Z" fill=""></path>
                                                                    </svg>
                                                                </button>
                                                                <div x-show="open" @click.outside="open = false" class="shadow-theme-lg dark:bg-gray-dark fixed w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800" x-ref="dropdown" style="display: none;">
                                                                    <button @click="isEditModalModal = true; editData = { id: '<?php echo $agent['id']; ?>', agent_name: '<?php echo $agent['name']; ?>', city: '<?php echo $agent['city']; ?>', phone: '<?php echo $agent['phone']; ?>', iframe_code: '<?php echo $agent['iframe_code']; ?>' }" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">تعديل</button>
                                                                    <a href="?delete_id=<?php echo $agent['id']; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page; ?>" onclick="return confirm('هل أنت متأكد من حذف مركز الصيانة؟')" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">حذف</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="px-5 py-8 text-center">
                                                    <div class="flex flex-col items-center justify-center p-8">
                                    
                                                        <h4 class="text-lg font-medium text-gray-700 dark:text-gray-300">لا توجد مراكز صيانة</h4>
                                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">لم يتم إضافة أي مراكز صيانة بعد</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if ($total_pages > 1): ?>
                            <div class="flex items-center justify-between border-t border-gray-200 px-5 py-4 sm:px-6 dark:border-gray-800">
                                <div class="text-theme-sm text-gray-500 dark:text-gray-400">
                                    عرض <?php echo min($per_page, count($agents)); ?> من <?php echo $total_agents; ?> نتيجة
                                </div>
                                <div class="flex gap-1">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" class="shadow-theme-xs flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                                            <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M10.0002 12.6667C9.9335 12.6667 9.86683 12.6534 9.80683 12.6267C9.74016 12.6 9.68016 12.56 9.6335 12.5067L6.1335 9.00668C5.96016 8.83335 5.96016 8.55335 6.1335 8.38002L9.6335 4.88002C9.74016 4.77335 9.90683 4.73335 10.0602 4.78002C10.2135 4.82668 10.3268 4.95335 10.3468 5.11335C10.3668 5.27335 10.2935 5.42668 10.1535 5.50668L7.02016 8.16668H13.3335C13.5668 8.16668 13.7668 8.36668 13.7668 8.60002C13.7668 8.83335 13.5668 9.03335 13.3335 9.03335H7.02016L10.1535 11.6934C10.3268 11.8667 10.3268 12.1467 10.1535 12.32C10.0468 12.4267 9.9135 12.48 9.78016 12.48L10.0002 12.6667Z" fill=""></path>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <?php if ($i == $page): ?>
                                            <span class="shadow-theme-xs flex h-9 w-9 items-center justify-center rounded-lg border border-brand-500 bg-brand-500 text-white"><?php echo $i; ?></span>
                                        <?php else: ?>
                                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="shadow-theme-xs flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"><?php echo $i; ?></a>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" class="shadow-theme-xs flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                                            <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M6.6665 12.6667C6.59984 12.6667 6.53317 12.6534 6.47317 12.6267C6.4065 12.6 6.3465 12.56 6.29984 12.5067C6.13317 12.3334 6.13317 12.0534 6.29984 11.88L9.4465 8.73337H3.33317C3.09984 8.73337 2.89984 8.53337 2.89984 8.30003C2.89984 8.0667 3.09984 7.8667 3.33317 7.8667H9.4465L6.29984 4.72003C6.1265 4.5467 6.1265 4.2667 6.29984 4.09337C6.47317 3.92003 6.75317 3.92003 6.9265 4.09337L10.4265 7.59337C10.5998 7.7667 10.5998 8.0467 10.4265 8.22003L6.9265 11.72C6.81984 11.8267 6.6865 11.88 6.55317 11.88L6.6665 12.6667Z" fill=""></path>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script defer src="./bundle.js"></script>
</body>
</html>