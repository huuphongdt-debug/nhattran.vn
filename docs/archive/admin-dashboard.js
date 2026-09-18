/*
|--------------------------------------------------------------------------
| SIDEBAR
|--------------------------------------------------------------------------
*/

const menuToggle =
    document.getElementById('menuToggle');

const sidebar =
    document.getElementById('sidebar');

const sidebarOverlay =
    document.getElementById('sidebarOverlay');


menuToggle.addEventListener(
    'click',
    function () {

        if (window.innerWidth <= 768) {

            sidebar.classList.toggle(
                'mobile-open'
            );

            sidebarOverlay.classList.toggle(
                'mobile-open'
            );

        } else {

            sidebar.classList.toggle(
                'collapsed'
            );

        }

    }
);


sidebarOverlay.addEventListener(
    'click',
    function () {

        sidebar.classList.remove(
            'mobile-open'
        );

        sidebarOverlay.classList.remove(
            'mobile-open'
        );

    }
);


/*
|--------------------------------------------------------------------------
| Khi chuyển từ mobile sang desktop
|--------------------------------------------------------------------------
*/

window.addEventListener(
    'resize',
    function () {

        if (window.innerWidth > 768) {

            sidebar.classList.remove(
                'mobile-open'
            );

            sidebarOverlay.classList.remove(
                'mobile-open'
            );

        }

    }
);
