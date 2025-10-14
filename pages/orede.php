<?php
require('../db.php');
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
$search = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}
$sql = "
    SELECT o.id AS order_id, o.userid, o.phone, o.name AS order_name,
           o.city, o.address, o.state, o.created_at AS order_date,
           c.id AS car_id, c.title AS car_title
    FROM `order` o
    LEFT JOIN car_cars c ON o.car_id = c.id
    WHERE o.id LIKE '%$search%'
       OR o.name LIKE '%$search%'
       OR o.phone LIKE '%$search%'
    ORDER BY o.created_at DESC
";
$result = $conn->query($sql);
$total_orders = 0;
$registered_users = 0;
$guest_users = 0;
$pending_orders = 0;
$completed_orders = 0;
$orders = [];
if ($result) {
    while ($order = $result->fetch_assoc()) {
        $orders[] = $order;
        if ($order['userid'] != 0) {
            $registered_users++;
        } else {
            $guest_users++;
        }
        if ($order['state'] == 'مكتمل' || strpos($order['state'], 'تصل') !== false) {
            $completed_orders++;
        } else {
            $pending_orders++;
        }
    }
    $total_orders = count($orders);
    $result->close();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $conn->real_escape_string($_POST['order_id']);
    $state = $conn->real_escape_string($_POST['state']);
    $update_sql = "UPDATE `order` SET state = '$state' WHERE id = '$order_id'";
    if ($conn->query($update_sql)) {
        echo "<script>alert('تم تحديث حالة الطلب بنجاح'); window.location.href = 'orders.php';</script>";
    } else {
        echo "<script>alert('حدث خطأ في تحديث حالة الطلب');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حجوزات السيارات - لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./main.css">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 99999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 20px;
            width: 90%;
            max-width: 700px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
        }
        .close {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .table th {
            background-color: #bc1e2c;
            color: white;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            display: inline-block;
        }
        .order-time {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .order-date {
            font-weight: bold;
            color: #495057;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
  

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        .form-select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            background-color: white;
        }
        .invoice-container {
        background-color: #fff;
        height: 100vh;
    display: flex
;
    align-items: center;
    justify-content: center;
    flex-direction: column;
        }
        .invoice-header {
  
            border-bottom: 2px solid #bc1e2c;
            padding-bottom: 10px;
        }
        .invoice-header img {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .invoice-details {
      
 
            margin-bottom: 20px;
        }
 
        .invoice-details p {
            margin: 5px 0;
            font-size: 14px;
        }
        .invoice-details strong {
            color: #374151;
        }
        .invoice-footer {
            text-align: center;
            margin-top: 20px;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }


.invoice-details div p {
    padding: 10px 0;
    border-bottom: 1px solid;
    display: flex
;
    align-items: center;
    justify-content: space-between;
}



span#detail_user_status,span#detail_state{
    padding: 0;
}
._flex {
    display: flex;
    align-items: center;
    justify-content: space-between;
}


p.text-sm.text-gray-500 {
    border: navajowhite;
}


span#detail_car,span#detail_state {
    display: inline-block;
    max-width: 80px; 
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: middle;
}
.invoice-details {width: 80%;}

        }

        @media screen and (max-width:992px) {
            

.invoice-container {
    padding: 25px;
}


.invoice-details {width: 100%;}

        }


        
        
    </style>
</head>
<body x-data="{ page: 'saas', loaded: true, darkMode: false, stickyMenu: false, sidebarToggle: false, scrollTop: false }" x-init="darkMode = JSON.parse(localStorage.getItem('darkMode')); $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))" :class="{'dark bg-gray-900': darkMode === true}">
    <div x-show="loaded" x-transition.opacity x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 500)})" class="fixed inset-0 z-999999 flex items-center justify-center bg-white dark:bg-black">
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
    </div>
    <div class="flex h-screen overflow-hidden">
        <div class="relative flex flex-1 flex-col overflow-x-hidden overflow-y-auto">
            <div :class="sidebarToggle ? 'block xl:hidden' : 'hidden'" class="fixed z-50 h-screen w-full bg-gray-900/50"></div>
            <main>
                <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
                    <div class="mb-8 flex flex-col justify-between gap-4 flex-row items-center">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">سجل الحجوزات</h3>
                    </div>
                    <div class="col-span-12">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-4">
                            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                                <div class="mb-6 flex h-[52px] w-[52px] items-center justify-center rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                    <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M20.3662 1.11216C20.6592 0.8193 21.134 0.819349 21.4269 1.11227C21.7198 1.4052 21.7197 1.88007 21.4268 2.17293L17.0308 6.56803C16.7379 6.8609 16.263 6.86085 15.9701 6.56792C15.6773 6.275 15.6773 5.80013 15.9702 5.50726L20.3662 1.11216ZM16.6592 2.696C16.952 2.40308 16.952 1.9282 16.659 1.63534C16.3661 1.34248 15.8913 1.34253 15.5984 1.63545L14.0987 3.13545C13.8058 3.42837 13.8059 3.90325 14.0988 4.19611C14.3917 4.48897 14.8666 4.48892 15.1595 4.196L16.6592 2.696ZM11.8343 3.45488C11.7079 3.19888 11.4472 3.0368 11.1617 3.0368C10.8762 3.0368 10.6155 3.19888 10.4892 3.45488L8.06431 8.36817L2.64217 9.15605C2.35966 9.19711 2.12495 9.39499 2.03673 9.6665C1.94851 9.93801 2.02208 10.2361 2.22651 10.4353L6.15001 14.2598L5.2238 19.66C5.17554 19.9414 5.29121 20.2258 5.52216 20.3936C5.75312 20.5614 6.05932 20.5835 6.31201 20.4506L11.1617 17.901L16.0114 20.4506C16.2641 20.5835 16.5703 20.5614 16.8013 20.3936C17.0322 20.2258 17.1479 19.9414 17.0996 19.66L16.1734 14.2598L20.0969 10.4353C20.3014 10.2361 20.3749 9.93801 20.2867 9.6665C20.1985 9.39499 19.9638 9.19711 19.6813 9.15605L14.2591 8.36817L11.8343 3.45488ZM9.23491 9.3856L11.1617 5.48147L13.0885 9.3856C13.1978 9.60696 13.4089 9.76039 13.6532 9.79588L17.9617 10.4219L14.8441 13.4609C14.6673 13.6332 14.5866 13.8814 14.6284 14.1247L15.3643 18.4158L11.5107 16.3898C11.2922 16.275 10.8127 16.3898L6.9591 18.4158L7.69508 14.1247C7.7368 13.8814 7.65614 13.6332 7.47938 13.4609L4.36174 10.4219L8.67021 9.79588C8.91449 9.76039 9.12567 9.60696 9.23491 9.3856ZM21.6514 5.12825C21.9443 5.42111 21.9444 5.89598 21.6515 6.18891L20.1518 7.68891C19.8589 7.98183 19.3841 7.98188 19.0912 7.68901C18.7982 7.39615 18.7982 6.92128 19.091 6.62836L20.5907 5.12836C20.8836 4.83543 21.3585 4.83538 21.6514 5.12825Z" fill=""></path>
                                    </svg>
                                </div>
                                <p class="text-theme-sm text-gray-500 dark:text-gray-400">إجمالي الطلبات</p>
                                <div class="mt-3 flex items-end justify-between">
                                    <div>
                                        <h4 class="text-title-sm font-bold text-gray-800 dark:text-white/90"><?php echo $total_orders; ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                                <div class="mb-6 flex h-[52px] w-[52px] items-center justify-center rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                    <svg class="fill-current" width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9.13768 5.60156C7.92435 5.60156 6.94074 6.58517 6.94074 7.79851C6.94074 9.01185 7.92435 9.99545 9.13768 9.99545C10.351 9.99545 11.3346 9.01185 11.3346 7.79851C11.3346 6.58517 10.351 5.60156 9.13768 5.60156ZM5.44074 7.79851C5.44074 5.75674 7.09592 4.10156 9.13768 4.10156C11.1795 4.10156 12.8346 5.75674 12.8346 7.79851C12.8346 9.84027 11.1795 11.4955 9.13768 11.4955C7.09592 11.4955 5.44074 9.84027 5.44074 7.79851ZM5.19577 15.3208C4.42094 16.0881 4.03702 17.0608 3.8503 17.8611C3.81709 18.0034 3.85435 18.1175 3.94037 18.2112C4.03486 18.3141 4.19984 18.3987 4.40916 18.3987H13.7582C13.9675 18.3987 14.1325 18.3141 14.227 18.2112C14.313 18.1175 14.3503 18.0034 14.317 17.8611C14.1303 17.0608 13.7464 16.0881 12.9716 15.3208C12.2153 14.572 11.0231 13.955 9.08367 13.955C7.14421 13.955 5.95202 14.572 5.19577 15.3208ZM4.14036 14.2549C5.20488 13.2009 6.78928 12.455 9.08367 12.455C11.3781 12.455 12.9625 13.2009 14.027 14.2549C15.0729 15.2906 15.554 16.5607 15.7778 17.5202C16.0991 18.8971 14.9404 19.8987 13.7582 19.8987H4.40916C3.22695 19.8987 2.06829 18.8971 2.38953 17.5202C2.6134 16.5607 3.09442 15.2906 4.14036 14.2549ZM15.6375 11.4955C14.8034 11.4955 14.0339 11.2193 13.4153 10.7533C13.7074 10.3314 13.9387 9.86419 14.0964 9.36432C14.493 9.75463 15.0371 9.99545 15.6375 9.99545C16.8508 9.99545 17.8344 9.01185 17.8344 7.79851C17.8344 6.58517 16.8508 5.60156 15.6375 5.60156C15.0371 5.60156 14.493 5.84239 14.0964 6.23271C13.9387 5.73284 13.7074 5.26561 13.4153 4.84371C14.0338 4.37777 14.8034 4.10156 15.6375 4.10156C17.6792 4.10156 19.3344 5.75674 19.3344 7.79851C19.3344 9.84027 17.6792 11.4955 15.6375 11.4955ZM20.2581 19.8987H16.7233C17.0347 19.4736 17.2492 18.969 17.3159 18.3987H20.2581C20.4674 18.3987 20.6323 18.3141 20.7268 18.2112C20.8129 18.1175 20.8501 18.0034 20.8169 17.861C20.6302 17.0607 20.2463 16.088 19.4714 15.3208C18.7379 14.5945 17.5942 13.9921 15.7563 13.9566C15.5565 13.6945 15.3328 13.437 15.0824 13.1891C14.8476 12.9566 14.5952 12.7384 14.3249 12.5362C14.7185 12.4831 15.1376 12.4549 15.5835 12.4549C17.8779 12.4549 19.4623 13.2008 20.5269 14.2549C21.5728 15.2906 22.0538 16.5607 22.2777 17.5202C22.5989 18.8971 21.4403 19.8987 20.2581 19.8987Z" fill=""></path>
                                    </svg>
                                </div>
                                <p class="text-theme-sm text-gray-500 dark:text-gray-400">عملاء مسجلين</p>
                                <div class="mt-3 flex items-end justify-between">
                                    <div>
                                        <h4 class="text-title-sm font-bold text-gray-800 dark:text-white/90"><?php echo $registered_users; ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                                <div class="mb-6 flex h-[52px] w-[52px] items-center justify-center rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                    <svg class="fill-current" width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M13.4164 2.79175C13.4164 2.37753 13.0806 2.04175 12.6664 2.04175C12.2522 2.04175 11.9164 2.37753 11.9164 2.79175V4.39876C9.94768 4.67329 8.43237 6.36366 8.43237 8.40795C8.43237 10.0954 9.47908 11.6058 11.0591 12.1984L13.7474 13.2066C14.7419 13.5795 15.4008 14.5303 15.4008 15.5925C15.4008 16.9998 14.2599 18.1407 12.8526 18.1407H11.7957C10.7666 18.1407 9.93237 17.3064 9.93237 16.2773C9.93237 15.8631 9.59659 15.5273 9.18237 15.5273C8.76816 15.5273 8.43237 15.8631 8.43237 16.2773C8.43237 18.1348 9.9382 19.6407 11.7957 19.6407H11.9164V21.2083C11.9164 21.6225 12.2522 21.9583 12.6664 21.9583C13.0806 21.9583 13.4164 21.6225 13.4164 21.2083V19.6017C15.3853 19.3274 16.9008 17.6369 16.9008 15.5925C16.9008 13.905 15.8541 12.3946 14.2741 11.8021L11.5858 10.7939C10.5912 10.4209 9.93237 9.47013 9.93237 8.40795C9.93237 7.00063 11.0732 5.85976 12.4806 5.85976H13.5374C14.5665 5.85976 15.4008 6.69401 15.4008 7.72311C15.4008 8.13732 15.7366 8.47311 16.1508 8.47311C16.565 8.47311 16.9008 8.13732 16.9008 7.72311C16.9008 5.86558 15.395 4.35976 13.5374 4.35976H13.4164V2.79175Z" fill=""></path>
                                    </svg>
                                </div>
                                <p class="text-theme-sm text-gray-500 dark:text-gray-400">عملاء زائرين</p>
                                <div class="mt-3 flex items-end justify-between">
                                    <div>
                                        <h4 class="text-title-sm font-bold text-gray-800 dark:text-white/90"><?php echo $guest_users; ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                                <div class="mb-6 flex h-[52px] w-[52px] items-center justify-center rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                    <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.58 20 4 16.42 4 12C4 7.58 7.58 4 12 4C16.42 4 20 7.58 20 12C20 16.42 16.42 20 12 20ZM11 7H13V13H11V7ZM11 15H13V17H11V15Z"></path>
                                    </svg>
                                </div>
                                <p class="text-theme-sm text-gray-500 dark:text-gray-400">طلبات قيد الانتظار</p>
                                <div class="mt-3 flex items-end justify-between">
                                    <div>
                                        <h4 class="text-title-sm font-bold text-gray-800 dark:text-white/90"><?php echo $pending_orders; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 mt-6">
                        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
                            <div class="flex flex-col gap-5 px-6 mb-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">حجوزات السيارات</h3>
                                </div>
                                <div class="flex items-center gap-3">
                                    <form method="GET" action="" class="flex">
                                        <div class="relative">
                                            <span class="absolute -translate-y-1/2 pointer-events-none top-1/2 left-4">
                                                <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" fill=""></path>
                                                </svg>
                                            </span>
                                            <input type="text" name="search" placeholder="ابحث برقم الطلب، الاسم أو الهاتف..." value="<?php echo htmlspecialchars($search); ?>" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="max-w-full overflow-x-auto custom-scrollbar">
                                <table class="min-w-full">
                                    <thead class="border-gray-100 border-y bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-6 py-3 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">رقم الطلب</p>
                                                </div>
                                            </th>
                                            <th class="px-6 py-3 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">اسم العميل</p>
                                                </div>
                                            </th>
                                            <th class="px-6 py-3 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">رقم الهاتف</p>
                                                </div>
                                            </th>
                                            <th class="px-6 py-3 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">المدينة</p>
                                                </div>
                                            </th>
                                            <th class="px-6 py-3 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">العنوان</p>
                                                </div>
                                            </th>
                                            <th class="px-6 py-3 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">السيارة</p>
                                                </div>
                                            </th>
                                            <th class="px-6 py-3 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">حالة المستخدم</p>
                                                </div>
                                            </th>
                                            <th class="px-6 py-3 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">حالة الطلب</p>
                                                </div>
                                            </th>
                                            <th class="px-6 py-3 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">التاريخ</p>
                                                </div>
                                            </th>
                                            <th class="px-6 py-3 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">الوقت</p>
                                                </div>
                                            </th>
                                            <th class="px-6 py-3 whitespace-nowrap">
                                                <div class="flex items-center justify-center">
                                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">الإجراءات</p>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <?php if ($total_orders > 0): ?>
                                            <?php foreach ($orders as $order):
                                                $order_datetime = $order['order_date'];
                                                $order_date = $order_datetime ? date('Y-m-d', strtotime($order_datetime)) : 'N/A';
                                                $order_time = $order_datetime ? date('H:i:s', strtotime($order_datetime)) : 'N/A';
                                            ?>
                                                <tr>
                                                    <td class="px-6 py-3 whitespace-nowrap">
                                                        <p class="text-gray-700 text-theme-sm dark:text-gray-400"><strong>#<?= htmlspecialchars($order['order_id']) ?></strong></p>
                                                    </td>
                                                    <td class="px-6 py-3 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <div class="flex items-center gap-3">
                                                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-brand-100">
                                                                    <span class="text-xs font-semibold text-brand-500"><?= substr(htmlspecialchars($order['order_name']), 0, 2) ?></span>
                                                                </div>
                                                                <div>
                                                                    <span class="text-theme-sm mb-0.5 block font-medium text-gray-700 dark:text-gray-400"><?= htmlspecialchars($order['order_name']) ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-3 whitespace-nowrap">
                                                        <p class="text-gray-700 text-theme-sm dark:text-gray-400">
                                                            <a href="tel:<?= htmlspecialchars($order['phone']) ?>" class="text-decoration-none">
                                                                <?= htmlspecialchars($order['phone']) ?>
                                                            </a>
                                                        </p>
                                                    </td>
                                                    <td class="px-6 py-3 whitespace-nowrap">
                                                        <p class="text-gray-700 text-theme-sm dark:text-gray-400"><?= htmlspecialchars($order['city']) ?></p>
                                                    </td>
                                                    <td class="px-6 py-3 whitespace-nowrap">
                                                        <p class="text-gray-700 text-theme-sm dark:text-gray-400"><?= htmlspecialchars($order['address']) ?></p>
                                                    </td>
                                                    <td class="px-6 py-3 whitespace-nowrap">
                                                        <?php if ($order['car_id'] && $order['car_title']): ?>
                                                            <a class="status-badge bg-blue-100 text-blue-800" href="https://hilaltiraimports.com/carsview.php?id=<?= urlencode($order['car_id']) ?>" target="_blank" rel="noopener" class="text-blue-600 hover:underline">
                                                                <?= htmlspecialchars($order['car_title']) ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-gray-400">N/A</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="px-6 py-3 whitespace-nowrap">
                                                        <?php if ($order['userid'] != 0): ?>
                                                            <span class="status-badge user-status-registered">مسجل</span>
                                                        <?php else: ?>
                                                            <span class="status-badge user-status-guest">زائر</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="px-6 py-3 whitespace-nowrap">
                                                        <span class="status-badge bg-blue-100 text-blue-800">
                                                            <?= htmlspecialchars($order['state']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-3 whitespace-nowrap">
                                                        <span class="status-badge bg-blue-100 text-blue-800""><?= $order_date ?></span>
                                                    </td>
                                                    <td class="px-6 py-3 whitespace-nowrap">
                                                        <span class="order-time"><?= $order_time ?></span>
                                                    </td>
                                                    <td class="px-6 py-3 whitespace-nowrap">
                                                        <div class="flex items-center justify-center gap-2">
                                                            <button type="button" class="btn btn-primary" onclick="openUpdateModal(<?= $order['order_id'] ?>, '<?= htmlspecialchars($order['state']) ?>')">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-primary" onclick="openDetailsModal(<?= $order['order_id'] ?>, '<?= htmlspecialchars($order['order_name']) ?>', '<?= htmlspecialchars($order['phone']) ?>', '<?= htmlspecialchars($order['city']) ?>', '<?= htmlspecialchars($order['address']) ?>', '<?= $order['userid'] != 0 ? 'مسجل' : 'زائر' ?>', '<?= htmlspecialchars($order['state']) ?>', '<?= $order_date ?>', '<?= $order_time ?>', '<?= htmlspecialchars($order['car_title'] ?? 'N/A') ?>')">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="11" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                                        <h4>لا توجد طلبات حالياً</h4>
                                                        <p>لم يتم تقديم أي طلبات حتى الآن.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="text-2xl font-semibold text-gray-800">تحديث حالة الطلب</h4>
                <span class="close" onclick="closeUpdateModal()">&times;</span>
            </div>
            <form method="post" action="">
                <input type="hidden" name="order_id" id="update_order_id">
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-700">حالة الطلب</label>
                    <select name="state" id="update_state" class="form-select" required>
                        <option value="تم حجز السياره">تم حجز السياره</option>
                        <option value="شحن السياره من كوريا الجنوبية الى الجمارك الكوريا">شحن السياره من كوريا الجنوبية الى الجمارك الكوريا</option>
                        <option value="شحن السياره من كوريا إلى جمارك السعوديه">شحن السياره من كوريا إلى جمارك السعوديه</option>
                        <option value="تصل للعميل من 8-15 يوم">تصل للعميل من 8-15 يوم</option>
                        <option value="مكتمل">مكتمل</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" class="btn btn-secondary" onclick="closeUpdateModal()">إلغاء</button>
                    <button type="submit" name="update_status" class="btn btn-primary">حفظ التحديث</button>
                </div>
            </form>
        </div>
    </div>
    <div id="detailsModal" class="modal">
        <div class="invoice-container">
    
            <div class="invoice-details">
            
                    <div class="invoice-header">
                <img src="https://via.placeholder.com/150" alt="Logo">
          
                
                <div class="_flex">
                
                      <h2 class="text-2xl font-bold text-gray-800">فاتورة الطلب</h2> 
                         <button type="button" class="btn btn-secondary mt-4" onclick="closeDetailsModal()">إغلاق</button></div>
                         
                <p class="text-sm text-gray-500">شركة هلال تيرا للاستيراد</p>
                
       
                
            </div>
            
            
                <div>
                    <h6 class="mb-3  mt-3 text-lg font-medium text-gray-700">معلومات العميل</h6>
                    <p><strong>الاسم:</strong> <span id="detail_name"></span></p>
                    <p><strong>الهاتف:</strong> <span id="detail_phone"></span></p>
                    <p><strong>المدينة:</strong> <span id="detail_city"></span></p>
                    <p><strong>العنوان:</strong> <span id="detail_address"></span></p>
                    <p><strong>حالة المستخدم:</strong> <span id="detail_user_status" class="status-badge"></span></p>
                </div>
                <div>
                    <h6 class="mb-3 text-lg font-medium text-gray-700">معلومات الطلب</h6>
                    <p><strong>رقم الطلب:</strong> <span id="detail_order_id"></span></p>
                    <p><strong>السيارة:</strong> <span id="detail_car"></span></p>
                    <p><strong>حالة الطلب:</strong> <span id="detail_state" class="status-badge bg-blue-100 text-blue-800"></span></p>
                    <p><strong>التاريخ:</strong> <span id="detail_date"></span></p>
                    <p><strong>الوقت:</strong> <span id="detail_time"></span></p>
                </div>
            </div>
    
        </div>
    </div>
    <script defer src="./bundle.js"></script>
    <script>
        function openUpdateModal(orderId, currentState) {
            document.getElementById('update_order_id').value = orderId;
            document.getElementById('update_state').value = currentState;
            document.getElementById('updateModal').style.display = 'block';
        }
        function closeUpdateModal() {
            document.getElementById('updateModal').style.display = 'none';
        }
        function openDetailsModal(orderId, name, phone, city, address, userStatus, state, date, time, car) {
            document.getElementById('detail_order_id').textContent = '#' + orderId;
            document.getElementById('detail_name').textContent = name;
            document.getElementById('detail_phone').textContent = phone;
            document.getElementById('detail_city').textContent = city;
            document.getElementById('detail_address').textContent = address;
            document.getElementById('detail_user_status').textContent = userStatus;
            document.getElementById('detail_user_status').className = userStatus === 'مسجل' ? 'status-badge user-status-registered' : 'status-badge user-status-guest';
            document.getElementById('detail_state').textContent = state;
            document.getElementById('detail_date').textContent = date;
            document.getElementById('detail_time').textContent = time;
            document.getElementById('detail_car').textContent = car;
            document.getElementById('detailsModal').style.display = 'block';
        }
        function closeDetailsModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }
        window.onclick = function(event) {
            const updateModal = document.getElementById('updateModal');
            const detailsModal = document.getElementById('detailsModal');
            if (event.target === updateModal) {
                closeUpdateModal();
            }
            if (event.target === detailsModal) {
                closeDetailsModal();
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Orders page loaded successfully');
        });
    </script>
</body>
</html>