(function () {
    var root = document.documentElement;
    var STORAGE_KEY = "sidebar-collapsed";
    var DESKTOP_MIN_WIDTH = 1200; // breakpoint xl Bootstrap 5

    function isDesktop() {
        return window.innerWidth >= DESKTOP_MIN_WIDTH;
    }

    function readState() {
        try {
            return localStorage.getItem(STORAGE_KEY) === "1";
        } catch (e) {
            return false;
        }
    }

    function writeState(collapsed) {
        try {
            localStorage.setItem(STORAGE_KEY, collapsed ? "1" : "0");
        } catch (e) {
            // localStorage diblokir: abaikan, toggle tetap jalan tanpa memori.
        }
    }

    // Terapkan status tersimpan sedini mungkin agar tidak berkedip saat halaman dimuat.
    if (isDesktop() && readState()) {
        root.classList.add("layout-menu-collapsed");
    }

    // Fase capture: berjalan sebelum handler bawaan Sneat, lalu menghentikannya
    // supaya klik tidak ter-toggle dua kali (buka lalu tutup lagi).
    document.addEventListener(
        "click",
        function (event) {
            var toggler = event.target.closest(".layout-menu-toggle");

            if (!toggler) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            if (isDesktop()) {
                writeState(root.classList.toggle("layout-menu-collapsed"));
            } else {
                // Layar kecil: menu tampil sebagai drawer. Overlay juga ber-class
                // layout-menu-toggle, jadi klik di luar menu otomatis menutupnya.
                root.classList.toggle("layout-menu-expanded");
            }
        },
        true
    );

    // Jika layar diperbesar saat drawer terbuka, tutup drawer-nya.
    window.addEventListener("resize", function () {
        if (isDesktop()) {
            root.classList.remove("layout-menu-expanded");
        }
    });
})();
