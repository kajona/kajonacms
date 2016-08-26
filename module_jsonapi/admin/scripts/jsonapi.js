

if (!KAJONA.admin) {
    KAJONA.admin = {};
}

if (!KAJONA.admin.jsonapi) {
    KAJONA.admin.jsonapi = {};
}

/**
 * Request a single object and calls the callback with the record as first argument
 *
 * @param {string} strSystemId
 * @param {callback} objCallback
 */
KAJONA.admin.jsonapi.get = function(strSystemId, objCallback){
    KAJONA.admin.ajax.genericAjaxCall("jsonapi", "dispatch", "&systemid=" + strSystemId, function(strData){
        var objData = JSON.parse(strData);
        var objRecord = new KAJONA.admin.jsonapi.record(objData);

        if (objCallback) {
            objCallback.apply(this, [objRecord]);
        }
    }, "GET");
};

/**
 * @param {string} strClass
 * @param {callback} objCallback
 */
KAJONA.admin.jsonapi.getAll = function(strClass, objCallback){
    KAJONA.admin.ajax.genericAjaxCall("jsonapi", "dispatch", "&class=" + strClass, function(strData){
        var objData = JSON.parse(strData);
        var objCollection = new KAJONA.admin.jsonapi.collection(objData);

        if (objCallback) {
            objCallback.apply(this, [objCollection]);
        }
    }, "GET");
};

/**
 * Model class which represents a record
 *
 * @param {object} raw
 */
KAJONA.admin.jsonapi.record = function(raw){
    var data = raw;

    this.getSystemId = function(){
        return data._id;
    };

    this.getClassName = function(){
        return data._class;
    };

    this.getIcon = function(){
        return data._icon;
    };

    this.getDisplayName = function(){
        return data._displayName;
    };

    this.getAdditionalInfo = function(){
        return data._additionalInfo;
    };

    this.getLongDescription = function(){
        return data._longDescription;
    };

    this.getProperty = function(strProperty){
        return data.hasOwnProperty(strProperty) ? data[strProperty] : null;
    };
};

/**
 * Model class which represents a collection of records
 *
 * @param {object} raw
 */
KAJONA.admin.jsonapi.collection = function(raw){
    var data = raw;
    var entries = [];

    // parse entries
    if (raw.entries) {
        for (var i = 0; i < raw.entries.length; i++) {
            entries.push(new KAJONA.admin.jsonapi.record(raw.entries[i]));
        }
    }

    this.getTotalCount = function(){
        return data.totalCount;
    };

    this.getStartIndex = function(){
        return data.startIndex;
    };

    this.getEntries = function(){
        return entries;
    };
};

