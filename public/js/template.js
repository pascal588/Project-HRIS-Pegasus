// Better jQuery dependency handling
if (typeof jQuery === "undefined") {
    console.error("jQuery is required but not loaded. Please include jQuery before this file.");
    // Instead of throwing error, we can wait for jQuery to load
    if (typeof $ === "undefined") {
        var script = document.createElement('script');
        script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
        script.integrity = 'sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=';
        script.crossOrigin = 'anonymous';
        script.onload = function() {
            initializeApp();
        };
        document.head.appendChild(script);
    }
} else {
    // jQuery is already loaded, initialize immediately
    $(document).ready(initializeApp);
}

function initializeApp() {
    "use strict";
    
    try {
        // main sidebar toggle js
        $('.menu-toggle').on('click', function () {
            $('.sidebar').toggleClass('open');
            $('.open').removeClass('sidebar-mini');
        });

        // layout a sidebar mini version
        $('.sidebar-mini-btn').on('click', function () {
            $('.sidebar').toggleClass('sidebar-mini');
            $('.sidebar-mini').removeClass('open');
        });

        // chat page chatlist toggle js - only if element exists
        if ($('.chatlist-toggle').length) {
            $('.chatlist-toggle').on('click', function () {
                $('.card-chat').toggleClass('open');
            });
        }

        // RTL theme toggle - only if element exists
        if ($(".theme-rtl input").length) {
            $(".theme-rtl input").on('change', function() {
                if (this.checked) {
                    $("body").addClass('rtl_mode');
                } else {
                    $("body").removeClass('rtl_mode');
                }
            });
        }

        // Sidebar overflow dynamic height
        overFlowDynamic();
        $(window).resize(overFlowDynamic);

        function overFlowDynamic() { 
            var $sidebar = $(".sidebar.sidebar-mini");
            if ($sidebar.length) {
                var sideheight = $sidebar.height() + 48;
                
                if (sideheight <= 760) {  
                    $sidebar.css("overflow", "scroll");  
                } else {
                    $sidebar.css("overflow", "visible"); 
                }
            }
        }

        // Dropdown scroll hide using table responsive
        $('.table-responsive').on('show.bs.dropdown', function () {
            $(this).css("overflow", "inherit");
        });
       
        $('.table-responsive').on('hide.bs.dropdown', function () {
            $(this).css("overflow", "auto");
        });

        // main theme color setting js
        $('.choose-skin li').on('click', function () {
            const $body = $('body');
            const $this = $(this);
            const existTheme = $('.choose-skin li.active').data('theme');
            
            $('.choose-skin li').removeClass('active');
            $this.addClass('active');
            
            // Remove all theme classes and add the new one
            $body.removeClass (function (index, className) {
                return (className.match (/(^|\s)theme-\S+/g) || []).join(' ');
            });
            $body.addClass('theme-' + $this.data('theme'));
        });

        // Monochrome Mode
        $('.monochrome-toggle input:checkbox').on('click', function () {
            if ($(this).is(":checked")) {
                $('body').addClass("monochrome");
            } else {
                $('body').removeClass("monochrome");
            }
        });

        // Light and dark theme setting js
        initializeThemeSwitcher();

    } catch (error) {
        console.error('Error initializing app:', error);
    }
}

function initializeThemeSwitcher() {
    var toggleSwitch = document.querySelector('.theme-switch input[type="checkbox"]');
    var toggleHcSwitch = document.querySelector('.theme-high-contrast input[type="checkbox"]');
    
    if (!toggleSwitch || !toggleHcSwitch) {
        console.warn('Theme switch elements not found');
        return;
    }

    var currentTheme = localStorage.getItem('theme');
    
    if (currentTheme) {
        document.documentElement.setAttribute('data-theme', currentTheme);
    
        if (currentTheme === 'dark') {
            toggleSwitch.checked = true;
            if (toggleHcSwitch) toggleHcSwitch.checked = false;
        } else if (currentTheme === 'high-contrast') {
            if (toggleHcSwitch) toggleHcSwitch.checked = true;
            toggleSwitch.checked = false;
        }
    }

    function switchTheme(e) {
        if (e.target.checked) {
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
            if (toggleHcSwitch) {
                toggleHcSwitch.checked = false;
                document.documentElement.removeAttribute('data-high-contrast');
            }
        } else {        
            document.documentElement.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
        }    
    }

    function switchHCTheme(e) {
        if (e.target.checked) {
            document.documentElement.setAttribute('data-theme', 'high-contrast');
            localStorage.setItem('theme', 'high-contrast');
            toggleSwitch.checked = false;
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
        }
    }

    toggleSwitch.addEventListener('change', switchTheme, false);
    if (toggleHcSwitch) {
        toggleHcSwitch.addEventListener('change', switchHCTheme, false);
    }
}

// Alternative initialization if jQuery loads separately
if (typeof jQuery !== "undefined" && typeof initializeApp === "function") {
    $(document).ready(initializeApp);
}