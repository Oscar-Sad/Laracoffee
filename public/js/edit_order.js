// ================= UTIL =================
const setVisible = (elementOrSelector, visible) =>
    ((typeof elementOrSelector === "string"
        ? document.querySelector(elementOrSelector)
        : elementOrSelector
    ).style.display = visible ? "block" : "none");

// ================= STATE =================
var isUseCoupon = false;
var couponTotal;
var currentNum = 0;
var couponUsed = 0;

var quantity;
var sub_total;
var total;
var shipping;

var product_id;
var destinasi;

// ================= INIT =================
$(document).ready(function () {
    quantity = parseInt($("#quantity").val());
    let price = parseInt($("#product_price").attr("data-truePrice"));

    sub_total = price * quantity;
    shipping = $("#total_price").val() - sub_total;

    $("#sub-total").html(sub_total);
    $("#shipping").attr("data-shippingCost", shipping);
    $("#shipping").html(shipping);

    // 🚀 START FLOW
    getLokasi();
});

// ================= COUPON =================
function changeStatesCoupon() {
    isUseCoupon = !isUseCoupon;
}

// ================= ORDER SUMMARY =================
function myCounter() {
    var num = parseInt($("#quantity").val());
    var price = parseInt($("#product_price").attr("data-truePrice"));
    shipping = parseInt($("#shipping").attr("data-shippingCost"));

    if (quantity != null && destinasi != null) {
        setOngkir({ destination: destinasi, quantity: num });
    }

    if (isUseCoupon && couponTotal > 0 && currentNum < num) {
        couponTotal--;
        couponUsed++;
    } else if (isUseCoupon && couponUsed > 0 && currentNum > num) {
        couponTotal++;
        couponUsed--;
    } else if (!isUseCoupon && couponUsed > 0) {
        couponTotal++;
        couponUsed--;
    }

    sub_total = price * (num - couponUsed);
    total = sub_total + shipping;

    $("#coupon").html(`${couponTotal} coupon`);
    $("#couponUsed").val(couponUsed);
    $("#couponUsedShow").html(`${couponUsed} coupon`);

    refresh_data({ sub_total, total });
    currentNum = num;
}

function refresh_data({ sub_total = 0, shipping = 0, total = 0 }) {
    if (total >= 0) {
        $("#total_price").val(total);
        $("#total").html(total);
    }
    if (sub_total >= 0) {
        $("#sub-total").html(sub_total);
    }
    if (shipping >= 0) {
        $("#shipping").attr("data-shippingCost", shipping);
        $("#shipping").html(shipping);
    }
}

// ================= ONGKIR =================

// 🔥 Load Province + Auto Select
function getLokasi() {
    let $op = $("#province");
    let selectedProvince = $op.data("selected"); // from HTML

    $.getJSON("/shipping/province", function (data) {
        $op.empty().append('<option value="">-- Select Province --</option>');

        $.each(data, function (i, field) {
            let selected = field.id == selectedProvince ? "selected" : "";

            $op.append(
                `<option value="${field.id}" ${selected}>
                    ${field.name}
                </option>`
            );
        });

        // ✅ AUTO LOAD CITY
        if (selectedProvince) {
            getCity(selectedProvince);
        }
    });
}

// 🔥 Load City + Auto Select + Trigger Ongkir
function getCity(province_id) {
    let op = $("#city");
    let selectedCity = op.data("selected");

    op.empty().append('<option value="">-- Select City --</option>');

    $.getJSON("/shipping/city/" + province_id, function (data) {
        $.each(data, function (i, field) {
            let selected = field.id == selectedCity ? "selected" : "";

            op.append(
                `<option value="${field.id}" ${selected}>
                    ${field.name}
                </option>`
            );
        });

        // ✅ AFTER CITY READY → TRIGGER ONGKIR
        if (selectedCity) {
            setCity();
        }
    });
}

// 🔥 When Province Changes
$("#province").on("change", function () {
    let province_id = $(this).val();

    $("#city").prop("disabled", !province_id);
    $("#city").data("selected", null); // reset selected

    if (province_id) {
        getCity(province_id);
    }
});

// 🔥 When City Changes
$("#city").on("change", function () {
    if ($(this).val()) {
        setCity();
    }
});

// 🔥 Set City → Call Ongkir
function setCity() {
    destinasi = $("#city").val();
    quantity = $("#quantity").val();

    setOngkir({
        destination: destinasi,
        quantity: quantity,
    });
}

// 🔥 Ongkir API Call
function setOngkir({
    origin = 42,
    destination,
    quantity,
    courier = "jne",
}) {
    if (!destination || quantity == 0) return;

    destination = parseInt(destination);
    quantity = parseInt(quantity);

    setVisible("#transaction", false);
    setVisible("#loading_transaction", true);

    $.ajax({
        url: `/shipping/cost/${origin}/${destination}/${quantity}/${courier}`,
        method: "GET",
        dataType: "json",

        success: function (data) {
            let city = $("#city option:selected");
            let province = $("#province option:selected");

            $("#shipping_address").val(
                city.text() + ", " + province.text()
            );

            let shipping = 0;

            if (
                data &&
                data[0] &&
                data[0].costs &&
                data[0].costs.length > 0 &&
                data[0].costs[0].cost &&
                data[0].costs[0].cost.length > 0
            ) {
                shipping = data[0].costs[0].cost[0].value;
            }

            total = sub_total + shipping;

            refresh_data({
                shipping: shipping,
                sub_total: sub_total,
                total: total,
            });

            setVisible("#transaction", true);
            setVisible("#loading_transaction", false);
        },

        error: function (xhr) {
            console.log("Error:", xhr.responseJSON);

            setVisible("#transaction", true);
            setVisible("#loading_transaction", false);
        },
    });
}

// ================= CANCEL ORDER =================
$("#button_edit_order").click(function (e) {
    e.preventDefault();

    Swal.fire({
        title: "Are you sure?",
        text: "order data will be changed",
        icon: "warning",
        confirmButtonText: "Confirm",
        cancelButtonColor: "#d33",
        showCancelButton: true,
        confirmButtonColor: "#08a10b",
        timer: 10000,
    }).then((result) => {
        if (result.isConfirmed) {
            $("#form_edit_order").submit();
        } else {
            Swal.fire("Action canceled", "", "info");
        }
    });
});
