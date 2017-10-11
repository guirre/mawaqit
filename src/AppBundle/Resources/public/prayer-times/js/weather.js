/**
 * handle weather
 */
var weather = {
    /**
     * get and display temperature
     */
    getTemperature: function () {
        $temperatureEl = $(".temperature");
        $temperatureEl.hide();
        $.ajax({
            url: $temperatureEl.data("remote"),
            success: function (resp) {
                if (resp != "") {
                    $temperatureEl.removeClass("blue orange red");
                    if (parseInt(resp) <= 15) {
                        $temperatureEl.addClass("blue");
                    } else if (parseInt(resp) > 15 && parseInt(resp) < 25) {
                        $temperatureEl.addClass("orange");
                    } else if (parseInt(resp) >= 25) {
                        $temperatureEl.addClass("red");
                    }
                    $(".temperature span").text(resp);
                    $temperatureEl.show();
                }
            },
            error: function () {
                $temperatureEl.hide();
            }
        });
    },
    initUpdateTemperature: function () {
        weather.getTemperature();
        setInterval(function () {
            weather.getTemperature();
        }, prayer.oneMinute * 60);
    }
};
