var test = angular.module("hackApp", ["ngResource"]);
test.controller('hackCtrl', myfunction);
function myfunction($scope) {
    $scope.censors = [{ "localtime": "2016-07-26 18:16:06.019102", "kiteid": "ch_kite_0001", "temperature": "7.0", "humidity": "73.0", "pressure": "97658.0", "luminosity": "14.0", "co2": "448.0", "sound": "13543.8" },
    { "localtime": "2016-07-26 18:16:42.842872", "kiteid": "ch_kite_0003", "temperature": "8.0", "humidity": "66.6", "pressure": "97928.8", "luminosity": "0.0", "co2": "440.4", "sound": "13605.0" },
    { "localtime": "2016-07-26 18:18:50.850587", "kiteid": "ch_kite_0001", "temperature": "7.0", "humidity": "73.0", "pressure": "97655.2", "luminosity": "14.0", "co2": "427.2", "sound": "13403.0" },
    { "localtime": "2016-07-26 18:18:56.265535", "kiteid": "ch_kite_0002", "temperature": "-1", "humidity": "-1", "pressure": "-1", "luminosity": "-33.0", "co2": "-1", "sound": "-1" },
    { "localtime": "2016-07-26 18:19:14.653978", "kiteid": "ch_kite_0003", "temperature": "8.0", "humidity": "67.0", "pressure": "97931.6", "luminosity": "0.0", "co2": "435.6", "sound": "13418.2" },
    { "localtime": "2016-07-26 18:19:46.881995", "kiteid": "ch_kite_0004", "temperature": "7.0", "humidity": "78.0", "pressure": "97724.6", "luminosity": "0.0", "co2": "462.6", "sound": "13320.4" },
    { "localtime": "2016-07-26 18:20:15.379623", "kiteid": "ch_kite_0001", "temperature": "7.0", "humidity": "73.0", "pressure": "97654.0", "luminosity": "14.0", "co2": "430.2", "sound": "13404.2" },
    { "localtime": "2016-07-26 18:20:30.207388", "kiteid": "ch_kite_0002", "temperature": "-1", "humidity": "-1", "pressure": "-1", "luminosity": "-33.0", "co2": "-1", "sound": "-1" },
    { "localtime": "2016-07-26 18:20:58.998574", "kiteid": "ch_kite_0003", "temperature": "8.0", "humidity": "66.6", "pressure": "97928.8", "luminosity": "0.0", "co2": "445.6", "sound": "13405.8" },
    { "localtime": "2016-07-26 18:21:28.938647", "kiteid": "ch_kite_0004", "temperature": "7.0", "humidity": "78.0", "pressure": "97711.8", "luminosity": "0.0", "co2": "465.8", "sound": "13332.8" }
    ]
 
    $scope.clicked = false;
    $scope.convertCSV = function () {
        $scope.clicked = true;
        for (var i = 0; i < $scope.censors.length; i++) {
            sparkline.push($scope.censors[i].temperature);
        }
        $(".line").text(sparkline);
        $(".line").peity("line");
    }
    $.fn.peity.defaults.line = {
        delimiter: ",",
        fill: "#c6d9fd",
        height: 160,
        max: null,
        min: 0,
        stroke: "#4d89f9",
        strokeWidth: 1,
        width: 320
    }
}
var sparkline = [];