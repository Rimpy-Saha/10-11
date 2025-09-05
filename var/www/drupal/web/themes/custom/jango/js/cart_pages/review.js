document.addEventListener("DOMContentLoaded", function() {
    var couponElement = document.getElementById("edit-sidebar-coupon-redemption-form");
    if (!couponElement) {
        var style = document.createElement('style');
        style.innerHTML = `.border { padding-bottom: 20px !important; }`;
        document.head.appendChild(style);
    }

    //adjustButtonMargin();
});


