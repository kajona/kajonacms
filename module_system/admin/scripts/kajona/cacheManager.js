
define([], function () {

    return {
        container: {},

        /**
         * @param {String} strKey
         * @return {String}
         */
        get: function(strKey){
            strKey = KAJONA_WEBPATH+"/"+strKey;
            if (localStorage) {
                return localStorage.getItem(strKey);
            }

            if (this.container[strKey]) {
                return this.container[strKey];
            }

            return false;
        },

        /**
         * @param {String} strKey
         * @param {String} strValue
         */
        set: function(strKey, strValue){
            strKey = KAJONA_WEBPATH+"/"+strKey;
            if (localStorage) {
                localStorage.setItem(strKey, strValue);
                return;
            }

            this.container[strKey] = strValue;
        }
    };

});


