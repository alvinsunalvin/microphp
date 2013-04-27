<?php
if (!function_exists('enableSelectDefault')) {
    function enableSelectDefault($return = false) {
        $js = '<script>
                var selects=document.getElementsByTagName("select");
                for(var k=0;k<selects.length;k++){
                    var s=selects[k];
                    var defaultv=s.attributes["default"]?s.attributes["default"].value:null;
                    if(defaultv){
                        for(var i=0;i<s.length;i++){
                        console.log(s[i].value);
                            if(s[i].value==defaultv){
                            s[i].selected=true;
                            break;
                            }
                        }
                    }
                };
            </script>';
        if ($return) {
            return $js;
        } else {
            echo $js;
        }
    }
}
