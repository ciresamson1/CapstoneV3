<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | PCMS</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body class="min-h-screen overflow-x-hidden bg-slate-100 text-slate-900">
    @yield('content')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('adminSidebar') || document.querySelector('aside');
            let setMobileClosedState = () => {};

            if (sidebar) {
                if (!sidebar.id) {
                    sidebar.id = 'adminSidebar';
                }

                sidebar.classList.remove('w-full');
                sidebar.classList.add(
                    'fixed',
                    'inset-y-0',
                    'left-0',
                    'z-40',
                    'w-80',
                    'max-w-[85vw]',
                    'overflow-y-auto',
                    'transition-transform',
                    'duration-200',
                    'xl:static',
                    'xl:min-h-screen',
                    'xl:w-80',
                    'xl:translate-x-0'
                );

                let backdrop = document.getElementById('adminSidebarBackdrop');
                if (!backdrop) {
                    backdrop = document.createElement('div');
                    backdrop.id = 'adminSidebarBackdrop';
                    backdrop.className = 'fixed inset-0 z-30 hidden bg-slate-950/60 xl:hidden';
                    sidebar.parentElement.insertBefore(backdrop, sidebar);
                }

                let openButtons = document.querySelectorAll('[data-sidebar-toggle]');

                if (!openButtons.length) {
                    const mobileTopBar = document.createElement('div');
                    mobileTopBar.className = 'flex items-center justify-between bg-slate-950 px-4 py-3 text-slate-100 xl:hidden';
                    const titleText = sidebar.querySelector('h1')?.textContent?.trim() || 'Navigation';
                    mobileTopBar.innerHTML = `
                        <div class="text-sm font-semibold">${titleText}</div>
                        <button type="button" data-sidebar-toggle class="inline-flex items-center justify-center rounded-xl border border-slate-700 p-2 text-slate-100" aria-label="Open navigation menu" aria-controls="adminSidebar" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        </button>
                    `;
                    sidebar.parentElement.insertBefore(mobileTopBar, sidebar.parentElement.firstChild);
                    openButtons = document.querySelectorAll('[data-sidebar-toggle]');
                }

                let closeButtons = document.querySelectorAll('[data-sidebar-close]');
                if (!closeButtons.length) {
                    const closeButton = document.createElement('button');
                    closeButton.type = 'button';
                    closeButton.setAttribute('data-sidebar-close', '');
                    closeButton.setAttribute('aria-label', 'Close navigation menu');
                    closeButton.className = 'mb-4 ml-auto inline-flex items-center justify-center rounded-xl border border-slate-700 p-2 text-slate-100 xl:hidden';
                    closeButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>';
                    sidebar.insertBefore(closeButton, sidebar.firstChild);
                    closeButtons = document.querySelectorAll('[data-sidebar-close]');
                }

                setMobileClosedState = () => {
                    if (window.innerWidth < 1280) {
                        sidebar.classList.add('-translate-x-full');
                    } else {
                        sidebar.classList.remove('-translate-x-full');
                        backdrop.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    }
                };

                const openSidebar = () => {
                    sidebar.classList.remove('-translate-x-full');
                    backdrop.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                    openButtons.forEach((button) => button.setAttribute('aria-expanded', 'true'));
                };

                const closeSidebar = () => {
                    sidebar.classList.add('-translate-x-full');
                    backdrop.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                    openButtons.forEach((button) => button.setAttribute('aria-expanded', 'false'));
                };

                openButtons.forEach((button) => {
                    button.addEventListener('click', openSidebar);
                });

                closeButtons.forEach((button) => {
                    button.addEventListener('click', closeSidebar);
                });

                backdrop.addEventListener('click', closeSidebar);

                sidebar.querySelectorAll('a').forEach((link) => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth < 1280) {
                            closeSidebar();
                        }
                    });
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && !backdrop.classList.contains('hidden')) {
                        closeSidebar();
                    }
                });
            }

            const isMobileView = () => window.innerWidth < 768;

            const updateSwipeGuide = (scroller, guide) => {
                if (!guide || !scroller) {
                    return;
                }

                const maxScrollLeft = scroller.scrollWidth - scroller.clientWidth;
                if (!isMobileView() || maxScrollLeft <= 8) {
                    guide.classList.add('hidden');
                    return;
                }

                guide.classList.remove('hidden');
                if (scroller.scrollLeft >= maxScrollLeft - 8) {
                    guide.textContent = 'Swipe right';
                } else {
                    guide.textContent = 'Swipe left';
                }
            };

            const initMobileTableSwipe = () => {
                const tables = document.querySelectorAll('table');

                tables.forEach((table, index) => {
                    let scroller = table.closest('[data-mobile-table-scroll], .overflow-x-auto');

                    if (!scroller || !scroller.contains(table)) {
                        scroller = document.createElement('div');
                        scroller.setAttribute('data-mobile-table-scroll', '');
                        table.parentNode.insertBefore(scroller, table);
                        scroller.appendChild(table);
                    } else {
                        scroller.setAttribute('data-mobile-table-scroll', '');
                    }

                    scroller.style.overflowX = isMobileView() ? 'auto' : '';
                    scroller.style.webkitOverflowScrolling = 'touch';
                    scroller.style.touchAction = 'pan-x';
                    scroller.style.overscrollBehaviorX = 'contain';

                    const cells = table.querySelectorAll('th, td');
                    if (isMobileView()) {
                        const targetMinWidth = Math.max(scroller.clientWidth + 240, 720);
                        table.style.minWidth = `${targetMinWidth}px`;
                        cells.forEach((cell) => {
                            if (!cell.hasAttribute('data-mobile-nowrap-applied')) {
                                cell.setAttribute('data-mobile-nowrap-applied', 'true');
                                cell.setAttribute('data-original-whitespace', cell.style.whiteSpace || '');
                            }
                            cell.style.whiteSpace = 'nowrap';
                        });
                    } else {
                        table.style.minWidth = '';
                        cells.forEach((cell) => {
                            if (cell.hasAttribute('data-mobile-nowrap-applied')) {
                                cell.style.whiteSpace = cell.getAttribute('data-original-whitespace') || '';
                            }
                        });
                    }

                    if (!scroller.dataset.tableSwipeId) {
                        scroller.dataset.tableSwipeId = `table-swipe-${index}`;
                    }

                    const guideSelector = `[data-mobile-swipe-guide-for="${scroller.dataset.tableSwipeId}"]`;
                    let guide = document.querySelector(guideSelector);

                    if (!guide) {
                        const guideHost = scroller.closest('.rounded-3xl.bg-white, .rounded-2xl.bg-white') || scroller.parentNode;
                        guideHost.classList.add('relative');

                        guide = document.createElement('p');
                        guide.setAttribute('data-mobile-swipe-guide-for', scroller.dataset.tableSwipeId);
                        guide.className = 'pointer-events-none absolute right-4 top-4 z-10 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-emerald-700 md:hidden';
                        guideHost.appendChild(guide);
                    }

                    if (!scroller.hasAttribute('data-swipe-listener-bound')) {
                        scroller.addEventListener('scroll', () => updateSwipeGuide(scroller, guide));
                        scroller.setAttribute('data-swipe-listener-bound', 'true');
                    }

                    updateSwipeGuide(scroller, guide);
                });
            };

            window.addEventListener('resize', () => {
                setMobileClosedState();
                initMobileTableSwipe();
            });

            setMobileClosedState();
            initMobileTableSwipe();
        });
    </script>
</body>
</html>
