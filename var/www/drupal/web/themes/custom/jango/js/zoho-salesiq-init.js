var $zoho = $zoho || {};
$zoho.salesiq = $zoho.salesiq || {
    widgetcode: "60ff663018d18af9d7578977d939e41677469f6ba2dcc0cf1fce595f0823c48702d70b64bb4127e6e9ca2080fa645f67",
    values: {},
    ready: function() {
        /* Optional: You can include additional configuration or widget initialization code here */
        /* For example: $zoho.salesiq.floatbutton.visible('hide'); */
    }
};

var d = document;
var s = d.createElement("script");
s.type = "text/javascript";
s.id = "zsiqscript";
s.defer = true;
s.src = "https://salesiq.zoho.com/widget";
var t = d.getElementsByTagName("script")[0];
t.parentNode.insertBefore(s, t);