<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة طبيب/استشاري جديد</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
    </style>
</head>
<body x-data="{ page: 'create-doctor', loaded: true, darkMode: false, stickyMenu: false, sidebarToggle: false, scrollTop: false, isTaskModalModal: false, days: ['السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'], selectedDays: [] }" x-init="darkMode = JSON.parse(localStorage.getItem('darkMode')); $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))" :class="{'dark bg-gray-900': darkMode === true}">
    <div x-show="loaded" x-transition.opacity x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 500)})" class="fixed inset-0 z-999999 flex items-center justify-center bg-white dark:bg-black">
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
    </div>

    <?php include '../includes/nav.php'; ?>
    
    <div class="flex h-screen overflow-hidden">
        <?php include '../includes/header.php'; ?>
        <div class="relative flex flex-1 flex-col overflow-x-hidden overflow-y-auto">
            <div x-show="isTaskModalModal" x-transition="" class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto z-99999">
                <div class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]" @click="isTaskModalModal=false"></div>
                <div @click.outside="isTaskModalModal=false" class="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-11">
                    <div class="px-2">
                        <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">إضافة طالب جديد</h4>
                        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">إدارة الطلاب بسهولة</p>
                    </div>
                    <form class="flex flex-col" method="POST">
                        <div class="custom-scrollbar overflow-y-auto px-2">
                            <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">اسم الطالب</label>
                                    <input type="text" name="name" value="" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">العنوان</label>
                                    <input type="text" name="address" value="" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">الهاتف</label>
                                    <input type="text" name="phone" value="01123428479" required pattern="[0-9]{11}" title="من فضلك أدخل رقم هاتف صحيح مكون من 11 رقم" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">البريد الإلكتروني</label>
                                    <input type="email" name="email" value="" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">الصف</label>
                                    <select name="grade_id" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                        <option value="">-- اختر صف --</option>
                                        <option value="1">الصف الأول الثانوي</option>
                                        <option value="2">الصف الثاني الثانوي</option>
                                        <option value="3">الصف الثالث الثانوي</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-center gap-6 px-2 mt-6 sm:flex-row sm:justify-between">
                            <div class="flex items-center w-full gap-3 sm:w-auto">
                                <button @click="isTaskModalModal=false" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto">إلغاء</button>
                                <button type="submit" name="add_student" class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">إضافة الطالب</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <main>
                <div class="mx-auto  p-4 md:p-6">
                    <div class="col-span-12">
                        <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                            <div class="flex justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Trending Stocks</h3>
                                <div class="stocks-slider-outer relative flex items-center gap-1.5">
                                    <div class="swiper-button-prev swiper-button-disabled">
                                        <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M10.1667 4L6 8.16667L10.1667 12.3333" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </div>
                                    <div class="swiper-button-next">
                                        <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5.83333 12.6667L10 8.50002L5.83333 4.33335" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="stocksSlider swiper swiper-initialized swiper-horizontal swiper-backface-hidden">
                                <div class="swiper-wrapper" style="transition-duration: 0ms; transform: translate3d(0px, 0px, 0px); transition-delay: 0ms;">
                                    <div class="swiper-slide swiper-slide-active" style="width: 550.5px; margin-right: 20px;">
                                        <div class="rounded-2xl bg-gray-100 p-5 dark:bg-white/[0.03]">
                                            <div class="flex items-center justify-between pb-5 mb-5 border-b border-gray-200 dark:border-gray-800">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10">
                                                        <img src="src/images/brand/brand-09.svg" alt="brand">
                                                    </div>
                                                    <div>
                                                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">TSLA</h3>
                                                        <span class="block text-gray-500 text-theme-xs dark:text-gray-400">Tesla, Inc</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="mb-1 font-medium text-right text-gray-700 text-theme-sm dark:text-gray-400">$192.53</h4>
                                                    <span class="flex items-center justify-end gap-1 font-medium text-theme-xs text-success-600 dark:text-success-500">
                                                        <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.56462 1.62394C5.70193 1.47073 5.90135 1.37433 6.12329 1.37433C6.1236 1.37433 6.12391 1.37433 6.12422 1.37433C6.31631 1.37416 6.50845 1.44732 6.65505 1.59381L9.65514 4.59181C9.94814 4.8846 9.94831 5.35947 9.65552 5.65247C9.36273 5.94546 8.88785 5.94563 8.59486 5.65284L6.87329 3.93248L6.87329 10.125C6.87329 10.5392 6.53751 10.875 6.12329 10.875C5.70908 10.875 5.37329 10.5392 5.37329 10.125L5.37329 3.93579L3.65516 5.65282C3.36218 5.94562 2.8873 5.94547 2.5945 5.65249C2.3017 5.3595 2.30185 4.88463 2.59484 4.59183L5.56462 1.62394Z" fill=""></path>
                                                        </svg>
                                                        1.01%
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button class="flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white p-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">Short Stock</button>
                                                <button class="flex items-center justify-center w-full p-3 font-medium text-white rounded-lg bg-brand-500 text-theme-sm shadow-theme-xs hover:bg-brand-600">Buy Stock</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="swiper-slide swiper-slide-next" style="width: 550.5px; margin-right: 20px;">
                                        <div class="rounded-2xl bg-gray-100 p-5 dark:bg-white/[0.03]">
                                            <div class="flex items-center justify-between pb-5 mb-5 border-b border-gray-200 dark:border-gray-800">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10">
                                                        <img src="src/images/brand/brand-07.svg" alt="brand">
                                                    </div>
                                                    <div>
                                                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">AAPL</h3>
                                                        <span class="block text-gray-500 text-theme-xs dark:text-gray-400">Apple, Inc</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="mb-1 font-medium text-right text-gray-700 text-theme-sm dark:text-gray-400">$192.53</h4>
                                                    <span class="flex items-center justify-end gap-1 font-medium text-theme-xs text-success-600 dark:text-success-500">
                                                        <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.56462 1.62394C5.70193 1.47073 5.90135 1.37433 6.12329 1.37433C6.1236 1.37433 6.12391 1.37433 6.12422 1.37433C6.31631 1.37416 6.50845 1.44732 6.65505 1.59381L9.65514 4.59181C9.94814 4.8846 9.94831 5.35947 9.65552 5.65247C9.36273 5.94546 8.88785 5.94563 8.59486 5.65284L6.87329 3.93248L6.87329 10.125C6.87329 10.5392 6.53751 10.875 6.12329 10.875C5.70908 10.875 5.37329 10.5392 5.37329 10.125L5.37329 3.93579L3.65516 5.65282C3.36218 5.94562 2.8873 5.94547 2.5945 5.65249C2.3017 5.3595 2.30185 4.88463 2.59484 4.59183L5.56462 1.62394Z" fill=""></path>
                                                        </svg>
                                                        3.59%
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button class="flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white p-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">Short Stock</button>
                                                <button class="flex items-center justify-center w-full p-3 font-medium text-white rounded-lg bg-brand-500 text-theme-sm shadow-theme-xs hover:bg-brand-600">Buy Stock</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="swiper-slide" style="width: 550.5px; margin-right: 20px;">
                                        <div class="rounded-2xl bg-gray-100 p-5 dark:bg-white/[0.03]">
                                            <div class="flex items-center justify-between pb-5 mb-5 border-b border-gray-200 dark:border-gray-800">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10">
                                                        <img src="src/images/brand/brand-11.svg" alt="brand">
                                                    </div>
                                                    <div>
                                                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">SPOT</h3>
                                                        <span class="block text-gray-500 text-theme-xs dark:text-gray-400">Spotify, Inc</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="mb-1 font-medium text-right text-gray-700 text-theme-sm dark:text-gray-400">$192.53</h4>
                                                    <span class="flex items-center justify-end gap-1 font-medium text-theme-xs text-success-600 dark:text-success-500">
                                                        <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.56462 1.62394C5.70193 1.47073 5.90135 1.37433 6.12329 1.37433C6.1236 1.37433 6.12391 1.37433 6.12422 1.37433C6.31631 1.37416 6.50845 1.44732 6.65505 1.59381L9.65514 4.59181C9.94814 4.8846 9.94831 5.35947 9.65552 5.65247C9.36273 5.94546 8.88785 5.94563 8.59486 5.65284L6.87329 3.93248L6.87329 10.125C6.87329 10.5392 6.53751 10.875 6.12329 10.875C5.70908 10.875 5.37329 10.5392 5.37329 10.125L5.37329 3.93579L3.65516 5.65282C3.36218 5.94562 2.8873 5.94547 2.5945 5.65249C2.3017 5.3595 2.30185 4.88463 2.59484 4.59183L5.56462 1.62394Z" fill=""></path>
                                                        </svg>
                                                        1.01%
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button class="flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white p-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">Short Stock</button>
                                                <button class="flex items-center justify-center w-full p-3 font-medium text-white rounded-lg bg-brand-500 text-theme-sm shadow-theme-xs hover:bg-brand-600">Buy Stock</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="swiper-slide" style="width: 550.5px; margin-right: 20px;">
                                        <div class="rounded-2xl bg-gray-100 p-5 dark:bg-white/[0.03]">
                                            <div class="flex items-center justify-between pb-5 mb-5 border-b border-gray-200 dark:border-gray-800">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10">
                                                        <img src="src/images/brand/brand-08.svg" alt="brand">
                                                    </div>
                                                    <div>
                                                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">PYPL</h3>
                                                        <span class="block text-gray-500 text-theme-xs dark:text-gray-400">Paypal, Inc</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="mb-1 font-medium text-right text-gray-700 text-theme-sm dark:text-gray-400">$192.53</h4>
                                                    <span class="flex items-center justify-end gap-1 font-medium text-theme-xs text-success-600 dark:text-success-500">
                                                        <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.56462 1.62394C5.70193 1.47073 5.90135 1.37433 6.12329 1.37433C6.1236 1.37433 6.12391 1.37433 6.12422 1.37433C6.31631 1.37416 6.50845 1.44732 6.65505 1.59381L9.65514 4.59181C9.94814 4.8846 9.94831 5.35947 9.65552 5.65247C9.36273 5.94546 8.88785 5.94563 8.59486 5.65284L6.87329 3.93248L6.87329 10.125C6.87329 10.5392 6.53751 10.875 6.12329 10.875C5.70908 10.875 5.37329 10.5392 5.37329 10.125L5.37329 3.93579L3.65516 5.65282C3.36218 5.94562 2.8873 5.94547 2.5945 5.65249C2.3017 5.3595 2.30185 4.88463 2.59484 4.59183L5.56462 1.62394Z" fill=""></path>
                                                        </svg>
                                                        1.01%
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button class="flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white p-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">Short Stock</button>
                                                <button class="flex items-center justify-center w-full p-3 font-medium text-white rounded-lg bg-brand-500 text-theme-sm shadow-theme-xs hover:bg-brand-600">Buy Stock</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="swiper-slide" style="width: 550.5px; margin-right: 20px;">
                                        <div class="rounded-2xl bg-gray-100 p-5 dark:bg-white/[0.03]">
                                            <div class="flex items-center justify-between pb-5 mb-5 border-b border-gray-200 dark:border-gray-800">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10">
                                                        <img src="src/images/brand/brand-10.svg" alt="brand">
                                                    </div>
                                                    <div>
                                                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">AMZN</h3>
                                                        <span class="block text-gray-500 text-theme-xs dark:text-gray-400">Amazon, Inc</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="mb-1 font-medium text-right text-gray-700 text-theme-sm dark:text-gray-400">$192.53</h4>
                                                    <span class="flex items-center justify-end gap-1 font-medium text-theme-xs text-success-600 dark:text-success-500">
                                                        <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.56462 1.62394C5.70193 1.47073 5.90135 1.37433 6.12329 1.37433C6.1236 1.37433 6.12391 1.37433 6.12422 1.37433C6.31631 1.37416 6.50845 1.44732 6.65505 1.59381L9.65514 4.59181C9.94814 4.8846 9.94831 5.35947 9.65552 5.65247C9.36273 5.94546 8.88785 5.94563 8.59486 5.65284L6.87329 3.93248L6.87329 10.125C6.87329 10.5392 6.53751 10.875 6.12329 10.875C5.70908 10.875 5.37329 10.5392 5.37329 10.125L5.37329 3.93579L3.65516 5.65282C3.36218 5.94562 2.8873 5.94547 2.5945 5.65249C2.3017 5.3595 2.30185 4.88463 2.59484 4.59183L5.56462 1.62394Z" fill=""></path>
                                                        </svg>
                                                        1.01%
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button class="flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white p-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">Short Stock</button>
                                                <button class="flex items-center justify-center w-full p-3 font-medium text-white rounded-lg bg-brand-500 text-theme-sm shadow-theme-xs hover:bg-brand-600">Buy Stock</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
                            <div class="mb-4 flex flex-col gap-2 px-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Latest Transactions</h3>
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                    <form method="GET" action="">
                                        <div class="relative">
                                            <span class="absolute -translate-y-1/2 pointer-events-none top-1/2 left-4">
                                                <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" fill=""></path>
                                                </svg>
                                            </span>
                                            <input type="text" name="search" placeholder="بحث..." value="" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
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
                                            إضافة طالب
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
                                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Name</p>
                                                </div>
                                            </th>
                                            <th class="px-5 py-3 font-normal whitespace-nowrap sm:px-6">
                                                <div class="flex items-center">
                                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Date</p>
                                                </div>
                                            </th>
                                            <th class="px-5 py-3 font-normal whitespace-nowrap sm:px-6">
                                                <div class="flex items-center">
                                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Price</p>
                                                </div>
                                            </th>
                                            <th class="px-5 py-3 font-normal whitespace-nowrap sm:px-6">
                                                <div class="flex items-center">
                                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Category</p>
                                                </div>
                                            </th>
                                            <th class="px-5 py-3 font-normal whitespace-nowrap sm:px-6">
                                                <div class="flex items-center">
                                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Status</p>
                                                </div>
                                            </th>
                                            <th class="px-5 py-3 font-normal whitespace-nowrap sm:px-6"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <tr>
                                            <td class="py-3 pr-5 whitespace-nowrap sm:pr-5">
                                                <div class="flex items-center">
                                                    <div class="flex items-center gap-3">
                                                        <div class="h-8 w-8">
                                                            <img src="src/images/brand/brand-08.svg" alt="brand">
                                                        </div>
                                                        <div>
                                                            <span class="text-theme-sm block font-medium text-gray-700 dark:text-gray-400">Bought PYPL</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-3 whitespace-nowrap sm:px-6">
                                                <div class="flex items-center">
                                                    <p class="text-theme-sm text-gray-700 dark:text-gray-400">Nov 23, 01:00 PM</p>
                                                </div>
                                            </td>
                                            <td class="px-5 py-3 whitespace-nowrap sm:px-6">
                                                <div class="flex items-center">
                                                    <p class="text-theme-sm text-gray-700 dark:text-gray-400">$2,567.88</p>
                                                </div>
                                            </td>
                                            <td class="px-5 py-3 whitespace-nowrap sm:px-6">
                                                <div class="flex items-center">
                                                    <p class="text-theme-sm text-gray-700 dark:text-gray-400">Finance</p>
                                                </div>
                                            </td>
                                            <td class="px-5 py-3 whitespace-nowrap sm:px-6">
                                                <div class="flex items-center">
                                                    <p class="bg-success-50 text-theme-xs text-success-600 dark:bg-success-500/15 dark:text-success-500 rounded-full px-2 py-0.5 font-medium">Success</p>
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
                                                            <button class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">View More</button>
                                                            <button class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Delete</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                                <div class="flex items-center justify-between">
                                    <button class="text-theme-sm shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-2 py-2 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 sm:px-3.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M2.58301 9.99868C2.58272 10.1909 2.65588 10.3833 2.80249 10.53L7.79915 15.5301C8.09194 15.8231 8.56682 15.8233 8.85981 15.5305C9.15281 15.2377 9.15297 14.7629 8.86018 14.4699L5.14009 10.7472L16.6675 10.7472C17.0817 10.7472 17.4175 10.4114 17.4175 9.99715C17.4175 9.58294 17.0817 9.24715 16.6675 9.24715L5.14554 9.24715L8.86017 5.53016C9.15297 5.23717 9.15282 4.7623 8.85983 4.4695C8.56684 4.1767 8.09197 4.17685 7.79917 4.46984L2.84167 9.43049C2.68321 9.568 2.58301 9.77087 2.58301 9.99715C2.58301 9.99766 2.58301 9.99817 2.58301 9.99868Z" fill=""></path>
                                        </svg>
                                        <span class="hidden sm:inline">Previous</span>
                                    </button>
                                    <span class="block text-sm font-medium text-gray-700 sm:hidden dark:text-gray-400">Page 1 of 10</span>
                                    <ul class="hidden items-center gap-0.5 sm:flex">
                                        <li>
                                            <a href="#" class="bg-brand-500/[0.08] text-theme-sm text-brand-500 hover:bg-brand-500/[0.08] hover:text-brand-500 dark:text-brand-500 dark:hover:text-brand-500 flex h-10 w-10 items-center justify-center rounded-lg font-medium">1</a>
                                        </li>
                                        <li>
                                            <a href="#" class="text-theme-sm hover:bg-brand-500/[0.08] hover:text-brand-500 dark:hover:text-brand-500 flex h-10 w-10 items-center justify-center rounded-lg font-medium text-gray-700 dark:text-gray-400">2</a>
                                        </li>
                                        <li>
                                            <a href="#" class="text-theme-sm hover:bg-brand-500/[0.08] hover:text-brand-500 dark:hover:text-brand-500 flex h-10 w-10 items-center justify-center rounded-lg font-medium text-gray-700 dark:text-gray-400">3</a>
                                        </li>
                                        <li>
                                            <a href="#" class="text-theme-sm hover:bg-brand-500/[0.08] hover:text-brand-500 dark:hover:text-brand-500 flex h-10 w-10 items-center justify-center rounded-lg font-medium text-gray-700 dark:text-gray-400">...</a>
                                        </li>
                                        <li>
                                            <a href="#" class="text-theme-sm hover:bg-brand-500/[0.08] hover:text-brand-500 dark:hover:text-brand-500 flex h-10 w-10 items-center justify-center rounded-lg font-medium text-gray-700 dark:text-gray-400">8</a>
                                        </li>
                                        <li>
                                            <a href="#" class="text-theme-sm hover:bg-brand-500/[0.08] hover:text-brand-500 dark:hover:text-brand-500 flex h-10 w-10 items-center justify-center rounded-lg font-medium text-gray-700 dark:text-gray-400">9</a>
                                        </li>
                                        <li>
                                            <a href="#" class="text-theme-sm hover:bg-brand-500/[0.08] hover:text-brand-500 dark:hover:text-brand-500 flex h-10 w-10 items-center justify-center rounded-lg font-medium text-gray-700 dark:text-gray-400">10</a>
                                        </li>
                                    </ul>
                                    <button class="text-theme-sm shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-2 py-2 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 sm:px-3.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                                        <span class="hidden sm:inline">Next</span>
                                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M17.4175 9.9986C17.4178 10.1909 17.3446 10.3832 17.198 10.53L12.2013 15.5301C11.9085 15.8231 11.4337 15.8233 11.1407 15.5305C10.8477 15.2377 10.8475 14.7629 11.1403 14.4699L14.8604 10.7472L3.33301 10.7472C2.91879 10.7472 2.58301 10.4114 2.58301 9.99715C2.58301 9.58294 2.91879 9.24715 3.33301 9.24715L14.8549 9.24715L11.1403 5.53016C10.8475 5.23717 10.8477 4.7623 11.1407 4.4695C11.4336 4.1767 11.9085 4.17685 12.2013 4.46984L17.1588 9.43049C17.3173 9.568 17.4175 9.77087 17.4175 9.99715C17.4175 9.99763 17.4175 9.99812 17.4175 9.9986Z" fill=""></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script defer src="../assets/js/bundle.js"></script>
</body>
</html>