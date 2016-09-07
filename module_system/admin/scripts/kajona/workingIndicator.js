
define(['jquery'], function ($) {

    var intWorkingCount = 0;

    return {
        start: function(){
            if(intWorkingCount == 0) {
                $('#status-indicator').addClass("active");
            }
            intWorkingCount++;
        },
        stop: function(){
            intWorkingCount--;

            if(intWorkingCount == 0) {
                $('#status-indicator').removeClass("active");
            }
        }
    };
});
