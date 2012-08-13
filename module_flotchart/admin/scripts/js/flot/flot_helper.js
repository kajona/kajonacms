/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var flotHelper = {};

flotHelper.getTickFormatter = function(angle, val) {
    /* Safari */
    var safari = "-webkit-transform: rotate("+angle+"deg); ";

    /* Firefox */
    var firefox = "-moz-transform: rotate("+angle+"deg); ";

    /* IE */
    var ie = "-ms-transform: rotate("+angle+"deg); ";

    /* Opera */
    var opera = "-o-transform: rotate("+angle+"deg);";
    
    return "<div style=\""+safari+firefox+ie+opera+"\">"+val+"</div>"
};


