
/**
 * Functions to execute system tasks
 */
define(["jquery", "ajax", "util", "statusDisplay"], function($, ajax, util, statusDisplay){

    var systemTask = {
        executeTask : function(strTaskname, strAdditionalParam, bitNoContentReset) {
            if(bitNoContentReset == null || bitNoContentReset == undefined) {

                if(document.getElementById('taskParamForm') != null) {
                    document.getElementById('taskParamForm').style.display = "none";
                }

                jsDialog_0.setTitle(KAJONA_SYSTEMTASK_TITLE);
                jsDialog_0.setContentRaw(kajonaSystemtaskDialogContent);
                $("#"+jsDialog_0.containerId).find("div.modal-dialog").removeClass("modal-lg");
                document.getElementById('systemtaskCancelButton').onclick = this.cancelExecution;
                jsDialog_0.init();
            }

            ajax.genericAjaxCall("system", "executeSystemTask", "&task="+strTaskname+strAdditionalParam, function(data, status, jqXHR) {
                if(status == 'success') {
                    var strResponseText = data;

                    //parse the response and check if it's valid
                    if(strResponseText.indexOf("<error>") != -1) {
                        statusDisplay.displayXMLMessage(strResponseText);
                    }
                    else if(strResponseText.indexOf("<statusinfo>") == -1) {
                        statusDisplay.messageError("<b>Request failed!</b><br />"+strResponseText);
                    }
                    else {
                        var intStart = strResponseText.indexOf("<statusinfo>")+12;
                        var strStatusInfo = strResponseText.substr(intStart, strResponseText.indexOf("</statusinfo>")-intStart);

                        //parse text to decide if a reload is necessary
                        var strReload = "";
                        if(strResponseText.indexOf("<reloadurl>") != -1) {
                            intStart = strResponseText.indexOf("<reloadurl>")+11;
                            strReload = strResponseText.substr(intStart, strResponseText.indexOf("</reloadurl>")-intStart);
                        }

                        //show status info
                        document.getElementById('systemtaskStatusDiv').innerHTML = strStatusInfo;
                        util.evalScript(strStatusInfo);

                        if(strReload == "") {
                            jsDialog_0.setTitle(KAJONA_SYSTEMTASK_TITLE_DONE);
                            document.getElementById('systemtaskLoadingDiv').style.display = "none";
                            document.getElementById('systemtaskCancelButton').value = KAJONA_SYSTEMTASK_CLOSE;
                        }
                        else {
                            systemTask.executeTask(strTaskname, strReload, true);
                        }
                    }
                }

                else {
                    jsDialog_0.hide();
                    statusDisplay.messageError("<b>Request failed!</b><br />"+data);
                }
            });
        },

        cancelExecution : function() {
            jsDialog_0.hide();
        },

        setName : function(strName) {
            document.getElementById('systemtaskNameDiv').innerHTML = strName;
        }
    };

    return systemTask;

});