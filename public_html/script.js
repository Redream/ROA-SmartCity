var test = angular.module("hackApp", ["ngResource"]);
test.controller('hackCtrl', myfunction);
function myfunction($scope, $resource) {
    var allInfo = $resource("http://roa.redream.co.nz/json.php?kite=:kiteid&type=:typeid");
    
    var param1 = "ch_kite_0001";
    $scope.param2 = "humidity";

    angular.element(document).ready(function () {
        $scope.specific = allInfo.query({kiteid: param1, typeid: $scope.param2}, chart);
    });

    function chart() {
        for (var i = 0; i < $scope.specific.length; i++) {
            Wait.push($scope.specific[i][$scope.param2]);
        }
        $(".line").text(Wait);
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
        width: 1135
    }
}
var Wait = [];
