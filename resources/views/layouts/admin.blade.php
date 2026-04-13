<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | PCMS</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    <style>
        /* ── Swipeable tables on mobile ─────────────────────────────────────
           Prevent cell wrapping so columns keep their natural widths.
           The overflow-x-auto wrapper (already on every table section) then
           shows a horizontal scrollbar / touch-swipe gesture on small screens.
        ──────────────────────────────────────────────────────────────────── */
        .overflow-x-auto th,
        .overflow-x-auto td {
            white-space: nowrap;
        }
        /* Description column in activity-log tables: allow wrapping but cap width */
        .overflow-x-auto td.td-description {
            white-space: normal;
            max-width: 260px;
        }
        /* Smooth momentum scroll on iOS for all swipe-tables */
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }
        /* Progress bar cells: explicit minimum so the bar is always readable */
        .overflow-x-auto td .progress-bar-inner {
            min-width: 100px;
        }
        /* ── Swipe hint badge (mobile only) ────────────────────────────────── */
        .table-swipe-hint {
            display: none;
        }
        @media (max-width: 1279px) {
            .table-swipe-hint {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                position: absolute;
                top: 14px;
                right: 16px;
                z-index: 10;
                background-color: #10b981;
                color: #ffffff;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 0.04em;
                padding: 4px 14px;
                border-radius: 9999px;
                user-select: none;
                pointer-events: none;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body class="min-h-screen overflow-x-hidden bg-slate-100 text-slate-900">
    @yield('content')

    {{-- Hamburger sidebar toggle (works with partials/sidebar.blade.php) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var sidebar   = document.getElementById('sidebar');
            var backdrop  = document.getElementById('sidebarBackdrop');
            var openBtn   = document.getElementById('sidebarOpen');
            var closeBtn  = document.getElementById('sidebarClose');

            function openSidebar() {
                if (!sidebar) return;
                sidebar.classList.remove('-translate-x-full');
                if (backdrop) backdrop.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeSidebar() {
                if (!sidebar) return;
                sidebar.classList.add('-translate-x-full');
                if (backdrop) backdrop.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            if (openBtn)  openBtn.addEventListener('click', openSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (backdrop) backdrop.addEventListener('click', closeSidebar);

            // Inject dynamic swipe hints – positioned absolute top-right of each card
            function updateHint(el, hint) {
                // Always hidden on desktop (xl+)
                if (window.innerWidth >= 1280) {
                    hint.style.display = 'none';
                    return;
                }
                var atLeft  = el.scrollLeft <= 1;
                var atRight = el.scrollLeft + el.clientWidth >= el.scrollWidth - 1;

                if (atLeft && atRight) {
                    hint.style.display = 'none';
                } else if (atLeft) {
                    hint.style.display = 'inline-flex';
                    hint.innerHTML = '&#8592;&nbsp;Swipe left';
                } else if (atRight) {
                    hint.style.display = 'inline-flex';
                    hint.innerHTML = 'Swipe right&nbsp;&#8594;';
                } else {
                    hint.style.display = 'inline-flex';
                    hint.innerHTML = '&#8592;&nbsp;Swipe left or right&nbsp;&#8594;';
                }
            }

            document.querySelectorAll('.overflow-x-auto').forEach(function (el) {
                if (!el.querySelector('table')) return;

                // Find nearest white card ancestor to place the badge top-right
                var card = el.closest('section')
                    || el.closest('[class*="rounded-3xl"]')
                    || el.parentElement;
                if (!card) return;

                // Card must be a positioning context for absolute child
                if (window.getComputedStyle(card).position === 'static') {
                    card.style.position = 'relative';
                }

                var hint = document.createElement('div');
                hint.className = 'table-swipe-hint';
                hint.setAttribute('aria-hidden', 'true');
                card.appendChild(hint);

                updateHint(el, hint);
                el.addEventListener('scroll',   function () { updateHint(el, hint); }, { passive: true });
                window.addEventListener('resize', function () { updateHint(el, hint); }, { passive: true });
            });
        });
    </script>
</body>
</html>
